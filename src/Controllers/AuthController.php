<?php
namespace App\Controllers;

use App\Http\Controller;
use App\Http\Request;
use App\Http\Response;
use App\Auth\Auth;

class AuthController extends Controller
{
    public function loginForm(Request $request): Response
    {
        if (Auth::check()) return $this->redirect('/admin');
        return $this->view('auth/login.php', ['titre' => 'Connexion']);
    }

    public function login(Request $request): Response
    {
        $v = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);
        if ($v->fails()) return $this->redirect('/login');

        if (Auth::attempt($request->post('email'), $request->post('password'))) {
            log_info('Connexion réussie', ['email' => $request->post('email')]);
            return $this->redirect('/admin');
        }

        session()->flash('error', 'Email ou mot de passe incorrect.');
        return $this->redirect('/login');
    }

    public function logout(Request $request): Response
    {
        Auth::logout();
        return $this->redirect('/');
    }

    public function registerForm(Request $request): Response
    {
        if (Auth::check()) return $this->redirect('/admin');
        return $this->view('auth/register.php', ['titre' => 'Inscription']);
    }

    public function register(Request $request): Response
    {
        $v = $request->validate([
            'nom'                   => 'required|min:2|max:100',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|min:8',
            'password_confirmation' => 'required|confirmed',
        ], [
            'email.unique'              => 'Cette adresse email est déjà utilisée.',
            'password.min'              => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password_confirmation.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        if ($v->fails()) return $this->redirect('/register');

        $token = Auth::register([
            'nom'      => $request->post('nom'),
            'email'    => $request->post('email'),
            'password' => $request->post('password'),
        ]);

        // Envoyer l'email de vérification
        $user  = \App\Database\DB::find('SELECT * FROM users WHERE id = ?', [$token]);
        mailer()->to($user['email'], $user['nom'])
              ->subject('Vérifiez votre adresse email — BlogPHP')
              ->view('emails/verify.php', ['user' => $user, 'token' => $user['token_verification']])
              ->send();

        session()->flash('success', 'Inscription réussie ! Vérifiez votre email.');
        return $this->redirect('/login');
    }

    public function verify(Request $request): Response
    {
        if (Auth::verifyEmail($request->param('token'))) {
            session()->flash('success', 'Email vérifié ! Vous pouvez vous connecter.');
        } else {
            session()->flash('error', 'Lien de vérification invalide ou expiré.');
        }
        return $this->redirect('/login');
    }

    public function forgotForm(Request $request): Response
    {
        return $this->view('auth/forgot.php', ['titre' => 'Mot de passe oublié']);
    }

    public function forgot(Request $request): Response
    {
        $v = $request->validate(['email' => 'required|email']);
        if ($v->fails()) return $this->redirect('/forgot-password');

        $token = Auth::generatePasswordReset($request->post('email'));
        if ($token) {
            $user = \App\Database\DB::find('SELECT * FROM users WHERE email = ?', [$request->post('email')]);
            mailer()->to($user['email'], $user['nom'])
                  ->subject('Réinitialisation de mot de passe — BlogPHP')
                  ->view('emails/reset.php', ['user' => $user, 'token' => $token])
                  ->send();
        }

        session()->flash('success', 'Si cet email existe, un lien de réinitialisation a été envoyé.');
        return $this->redirect('/forgot-password');
    }

    public function resetForm(Request $request): Response
    {
        return $this->view('auth/reset.php', ['titre' => 'Nouveau mot de passe', 'token' => $request->param('token')]);
    }

    public function reset(Request $request): Response
    {
        $v = $request->validate([
            'password'              => 'required|min:8',
            'password_confirmation' => 'required|confirmed',
        ]);
        if ($v->fails()) return $this->redirect('/reset/' . $request->param('token'));

        if (Auth::resetPassword($request->param('token'), $request->post('password'))) {
            session()->flash('success', 'Mot de passe modifié ! Connectez-vous.');
            return $this->redirect('/login');
        }

        session()->flash('error', 'Lien expiré ou invalide.');
        return $this->redirect('/forgot-password');
    }
}
