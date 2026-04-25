<?php
namespace App\Controllers;

use App\Http\Controller;
use App\Http\Request;
use App\Http\Response;
use App\Models\Article;
use App\Models\Categorie;
use App\Models\Commentaire;
use App\Exceptions\HttpException;

class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('blog/index.php', [
            'titre'    => 'Blog',
            'articles' => Article::published(),
        ]);
    }

    public function show(Request $request): Response
    {
        $article = Article::findBySlug($request->param('slug'));
        if (!$article) throw new HttpException(404, 'Article introuvable');

        if ($request->isPost()) {
            $errors = $request->validate([
                'auteur'  => 'required|min:2',
                'email'   => 'required|email',
                'contenu' => 'required|min:5',
            ]);
            if (empty($errors)) {
                Commentaire::create([
                    'article_id' => $article['id'],
                    'auteur'     => htmlspecialchars($request->post('auteur')),
                    'email'      => htmlspecialchars($request->post('email')),
                    'contenu'    => htmlspecialchars($request->post('contenu')),
                    'approuve'   => 0,
                ]);
                return $this->redirect('/article/' . $article['slug'] . '#commentaires');
            }
        }

        return $this->view('blog/show.php', [
            'titre'        => $article['titre'],
            'article'      => $article,
            'commentaires' => Commentaire::forArticle($article['id']),
        ]);
    }

    public function categorie(Request $request): Response
    {
        $cat = Categorie::findBySlug($request->param('slug'));
        if (!$cat) throw new HttpException(404, 'Catégorie introuvable');

        return $this->view('blog/index.php', [
            'titre'     => 'Catégorie : ' . $cat['nom'],
            'articles'  => Article::byCategory($cat['id']),
            'categorie' => $cat,
        ]);
    }

    public function recherche(Request $request): Response
    {
        $q        = trim($request->get('q', ''));
        $articles = strlen($q) >= 2 ? Article::search($q) : [];

        return $this->view('blog/index.php', [
            'titre'     => 'Recherche : ' . htmlspecialchars($q),
            'articles'  => $articles,
            'recherche' => $q,
        ]);
    }
}
