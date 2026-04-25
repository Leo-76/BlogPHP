<?php
namespace App\Controllers;

use App\Http\Controller;
use App\Http\Request;
use App\Http\Response;
use App\Models\Article;
use App\Models\Categorie;
use App\Models\Commentaire;
use App\Auth\Auth;
use App\Database\DB;

class AdminController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $stats = cache()->remember('admin.stats', 60, fn() => [
            'articles'     => Article::count(),
            'commentaires' => count(Commentaire::pending()),
            'categories'   => Categorie::count(),
            'users'        => DB::find('SELECT COUNT(*) as n FROM users')['n'],
        ]);
        return $this->view('admin/index.php', ['titre' => 'Dashboard', 'stats' => $stats]);
    }

    public function articles(Request $request): Response
    {
        $articles = paginate(
            'SELECT a.*, c.nom as categorie_nom FROM articles a LEFT JOIN categories c ON a.categorie_id=c.id ORDER BY a.created_at DESC',
            [], 15
        );
        return $this->view('admin/articles.php', ['titre' => 'Articles', 'articles' => $articles]);
    }

    public function newForm(Request $request): Response
    {
        return $this->view('admin/form.php', [
            'titre'        => 'Nouvel article',
            'article'      => null,
            'categories'   => Categorie::all(),
            'commentaires' => [],
        ]);
    }

    public function create(Request $request): Response
    {
        $v = $request->validate([
            'titre'   => 'required|min:3|max:255',
            'contenu' => 'required|min:10',
        ]);
        if ($v->fails()) return $this->redirect('/admin/article/new');

        $image = null;
        if ($request->hasFile('image')) {
            $file   = $request->file('image');
            $errors = $file->validate(['image' => true, 'max_size' => 2048, 'mimes' => ['jpg','jpeg','png','webp']]);
            if ($errors) { session()->flash('error', implode(' ', $errors)); return $this->redirect('/admin/article/new'); }
            $image = $file->store('articles');
        }

        Article::create([
            'titre'        => $request->post('titre'),
            'slug'         => Article::makeSlug($request->post('titre')),
            'extrait'      => $request->post('extrait', ''),
            'contenu'      => $request->post('contenu'),
            'image'        => $image,
            'categorie_id' => $request->post('categorie_id') ?: null,
            'publie'       => $request->post('publie') ? 1 : 0,
        ]);
        cache()->flush();
        session()->flash('success', 'Article créé avec succès.');
        return $this->redirect('/admin/articles');
    }

    public function editForm(Request $request): Response
    {
        $article = Article::find((int)$request->param('id'));
        if (!$article) return $this->redirect('/admin/articles');
        return $this->view('admin/form.php', [
            'titre'        => 'Modifier : ' . $article['titre'],
            'article'      => $article,
            'categories'   => Categorie::all(),
            'commentaires' => Commentaire::where('article_id', $article['id']),
        ]);
    }

    public function update(Request $request): Response
    {
        $id = (int)$request->param('id');

        if ($request->post('approuver_commentaire')) {
            Commentaire::update((int)$request->post('approuver_commentaire'), ['approuve' => 1]);
            return $this->redirect('/admin/article/' . $id);
        }
        if ($request->post('supprimer_commentaire')) {
            Commentaire::delete((int)$request->post('supprimer_commentaire'));
            return $this->redirect('/admin/article/' . $id);
        }

        $image = null;
        if ($request->hasFile('image')) {
            $file   = $request->file('image');
            $errors = $file->validate(['image' => true, 'max_size' => 2048]);
            if (!$errors) $image = $file->store('articles');
        }

        $data = [
            'titre'        => $request->post('titre'),
            'extrait'      => $request->post('extrait', ''),
            'contenu'      => $request->post('contenu'),
            'categorie_id' => $request->post('categorie_id') ?: null,
            'publie'       => $request->post('publie') ? 1 : 0,
        ];
        if ($image) $data['image'] = $image;

        Article::update($id, $data);
        cache()->flush();
        session()->flash('success', 'Article mis à jour.');
        return $this->redirect('/admin/articles');
    }

    public function delete(Request $request): Response
    {
        Article::delete((int)$request->param('id'));
        cache()->flush();
        session()->flash('success', 'Article supprimé.');
        return $this->redirect('/admin/articles');
    }

    public function profil(Request $request): Response
    {
        return $this->view('admin/profil.php', ['titre' => 'Mon profil', 'user' => Auth::user()]);
    }

    public function updateProfil(Request $request): Response
    {
        $v = $request->validate([
            'nom'   => 'required|min:2',
            'email' => 'required|email',
        ]);
        if ($v->fails()) return $this->redirect('/admin/profil');

        $data = ['nom' => $request->post('nom'), 'email' => $request->post('email')];

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            if ($file->isValid()) $data['avatar'] = $file->store('avatars');
        }

        if ($request->post('password')) {
            $v2 = $request->validate(['password' => 'min:8', 'password_confirmation' => 'confirmed']);
            if ($v2->passes()) $data['password'] = password_hash($request->post('password'), PASSWORD_DEFAULT);
        }

        DB::execute('UPDATE users SET nom=?, email=?' . (isset($data['avatar']) ? ', avatar=?' : '') . ' WHERE id=?',
            array_values(array_merge(
                [$data['nom'], $data['email']],
                isset($data['avatar']) ? [$data['avatar']] : [],
                [Auth::id()]
            ))
        );

        session()->set('user_nom', $data['nom']);
        session()->flash('success', 'Profil mis à jour.');
        return $this->redirect('/admin/profil');
    }
}
