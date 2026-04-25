<?php
namespace App\Controllers\Api;

use App\Api\ApiController;
use App\Http\Request;
use App\Http\Response;
use App\Models\Article;
use App\Database\DB;

class ArticleApiController extends ApiController
{
    public function index(Request $request): Response
    {
        $perPage  = (int)$request->get('per_page', 10);
        $articles = cache()->remember('api.articles.page.' . ($request->get('page', 1)), 300, function() use ($perPage) {
            return paginate('SELECT a.*, c.nom as categorie FROM articles a LEFT JOIN categories c ON a.categorie_id=c.id WHERE a.publie=1 ORDER BY a.created_at DESC', [], $perPage);
        });
        return $this->json([
            'data' => $articles->items,
            'meta' => [
                'current_page' => $articles->currentPage,
                'last_page'    => $articles->lastPage,
                'total'        => $articles->total,
                'per_page'     => $articles->perPage,
            ],
        ]);
    }

    public function show(Request $request): Response
    {
        $article = Article::findBySlug($request->param('slug'));
        if (!$article) return $this->notFound('Article introuvable.');
        return $this->success($article);
    }

    public function categories(Request $request): Response
    {
        return $this->success(DB::query('SELECT * FROM categories ORDER BY nom'));
    }

    public function store(Request $request): Response
    {
        $errors = $this->validate($request, [
            'titre'   => 'required|min:3',
            'contenu' => 'required|min:10',
        ]);
        if ($errors) return $this->error('Données invalides.', 422, $errors);

        $id = Article::create([
            'titre'        => $request->json()['titre'],
            'slug'         => Article::makeSlug($request->json()['titre']),
            'extrait'      => $request->json()['extrait'] ?? '',
            'contenu'      => $request->json()['contenu'],
            'categorie_id' => $request->json()['categorie_id'] ?? null,
            'publie'       => $request->json()['publie'] ?? 1,
        ]);
        cache()->forget('api.articles.page.1');
        return $this->created(Article::find((int)$id));
    }

    public function destroy(Request $request): Response
    {
        $article = Article::findBySlug($request->param('slug'));
        if (!$article) return $this->notFound();
        Article::delete($article['id']);
        cache()->flush();
        return $this->noContent();
    }
}
