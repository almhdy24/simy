# Simy Framework 🚀

A minimal, dependency-free PHP framework for building web applications and APIs.

## ✨ Why Simy?

- **Zero Dependencies** - Pure PHP, no bloat
- **PSR-7 Ready** - Built-in HTTP message implementation  
- **Modern PHP 8** - Leverages latest PHP features
- **Lightning Fast** - Minimal overhead, maximum performance
- **Easy Learning** - Simple and intuitive API

## 🚀 Get Started in 60 Seconds

### 1. Download & Install
```bash
git clone https://github.com/almhdy24/simy.git
cd simy
```

2. Create Your First Route

Edit routes/web.php:

```php
$route->get('/', function() {
    return 'Hello World! 🎉';
});
```

3. Start Developing

```bash
php -S localhost:8000 -t public
```

Open http://localhost:8000 and see your app running!

💡 Examples

Basic Routing

```php
// Simple response
$route->get('/hello', fn() => 'Hello Simy!');

// JSON API response
$route->get('/api/users', fn() => [
    'users' => [
        ['id' => 1, 'name' => 'John'],
        ['id' => 2, 'name' => 'Jane']
    ]
]);

// Route parameters
$route->get('/user/{id}', fn($req) => 
    "User ID: " . $req->getAttribute('id')
);
```

Handle Form Data

```php
$route->post('/contact', function($request) {
    $data = $request->getParsedBody();
    return "Hello, " . ($data['name'] ?? 'Guest');
});
```

🏗️ Project Structure

```
your-app/
├── app/           # Your controllers & providers
├── core/          # Framework core (PSR-7, Router, DI)
├── public/        # Web server root
├── routes/        # web.php & api.php
└── storage/       # Logs & cache (auto-created)
```

🛠️ Development Commands

```bash
# Run tests
composer test

# Start development server  
composer serve

# Run with custom port
php -S localhost:3000 -t public
```

📖 Learn More

· Full Documentation - Detailed guides and examples
· API Reference - Complete class reference
· GitHub Repository - Star us! ⭐

🤝 Support

Found a bug? Have a question?
Create an issue on GitHub!

---

Built with ❤️ by Elmahdi Abdallh
MIT Licensed - Free for personal and commercial use


