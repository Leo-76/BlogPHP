# BlogPHP — avec composants Symfony

Framework PHP maison utilisant les vrais composants Symfony.

## Installation

```bash
composer install
php -S localhost:8080 router.php
```

## Composants Symfony utilisés

| Composant | Package | Remplace |
|---|---|---|
| **HttpFoundation** | `symfony/http-foundation` | Request, Response, Session, Cookie |
| **Routing** | `symfony/routing` | Notre Router maison |
| **Validator** | `symfony/validator` | Notre Validator maison |
| **Cache** | `symfony/cache` (PSR-16) | Notre Cache maison |
| **Mailer** | `symfony/mailer` | Notre Mailer maison |
| **PasswordHasher** | `symfony/password-hasher` | `password_hash()` maison |
| **DBAL** | `doctrine/dbal` | Notre DB.php maison |

## Ce qui reste maison

| Composant | Fichier |
|---|---|
| Container IoC | `src/Core/Container.php` |
| Application Kernel | `src/Core/Application.php` |
| Vues PHP natif | `src/View/TwigEngine.php` |
| Auth | `src/Auth/Auth.php` |
| Console CLI | `bin/console` |
| Models | `src/Models/` |

## Exemples — comme Symfony

### Request (HttpFoundation)
```php
$request->query->get('page')      // GET params
$request->request->get('titre')   // POST params
$request->files->get('image')     // Fichiers
$request->headers->get('Accept')  // Headers
$request->isMethod('POST')
$request->getPathInfo()
```

### Session (HttpFoundation)
```php
session()->set('user_id', 1);
session()->get('user_id');
session()->getFlashBag()->add('success', 'Enregistré !');
session()->migrate(true);  // Régénère l'ID
session()->invalidate();   // Détruit
```

### Validator (symfony/validator)
```php
$v = validate($request->all(), [
    'email'    => 'required|email|unique:users,email',
    'password' => 'required|min:8|confirmed',
    'age'      => 'required|min_val:18|max_val:120',
    'image'    => 'image',
    'site'     => 'url',
]);

if ($v->fails()) {
    session()->flash('errors', $v->errors());
    return redirect('/form');
}
```

### Cache (symfony/cache PSR-16)
```php
cache()->remember('articles', 300, fn() => Article::published());
cache()->set('key', 'value', 3600);
cache()->get('key', 'default');
cache()->forget('key');
cache()->flush();
```

### Mailer (symfony/mailer)
```php
mailer()
    ->to('jean@email.com', 'Jean')
    ->subject('Bienvenue !')
    ->view('emails/welcome.php', ['user' => $user])
    ->send();
```

### Password Hasher (symfony/password-hasher)
```php
sf_bcrypt('mon-mot-de-passe');
sf_hash_check('mot-de-passe', $hash);
```

### DB (doctrine/dbal)
```php
// API compatible maison
DB::query('SELECT * FROM articles WHERE publie = ?', [1]);
DB::find('SELECT * FROM articles WHERE slug = ?', [$slug]);
DB::execute('INSERT INTO articles ...', [...]);

// Query Builder Doctrine
DB::table('articles')
   ->select('*')
   ->where('publie = 1')
   ->orderBy('created_at', 'DESC')
   ->fetchAllAssociative();

// Transactions
DB::transaction(function($conn) {
    $conn->insert('articles', [...]);
    $conn->insert('tags', [...]);
});
```

### Routing (symfony/routing)
```php
// routes/web.php — même syntaxe que BlogPHP
$router->get('/', 'BlogController@index');
$router->get('/article/{slug}', 'BlogController@show');
$router->group(['prefix' => '/admin'], function($router) {
    $router->get('', 'AdminController@dashboard', [AuthMiddleware::class]);
});
```

## Console CLI

```bash
php bin/console migrate
php bin/console migrate:fresh
php bin/console migrate:rollback
php bin/console make:controller MonController
php bin/console make:model MonModel --migration
php bin/console routes
php bin/console cache:clear
```

## Configuration .env

```env
APP_NAME="BlogPHP"
APP_DEBUG=true

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

MAIL_DRIVER=log
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_FROM_ADDRESS=no-reply@blogphp.com
```

## License
MIT © 
