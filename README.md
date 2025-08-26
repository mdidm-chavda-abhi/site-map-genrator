<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laravel Sitemap Generator - Documentation</title>
<style>
    body {
        font-family: "Segoe UI", Tahoma, sans-serif;
        background-color: #f8f9fa;
        color: #333;
        margin: 0;
        padding: 0;
        line-height: 1.6;
    }
    header {
        background: #1f2937;
        color: #fff;
        text-align: center;
        padding: 30px 20px;
    }
    header h1 {
        margin: 0;
        font-size: 2.5rem;
    }
    header p {
        font-size: 1.1rem;
        margin-top: 10px;
        opacity: 0.9;
    }
    .container {
        max-width: 1000px;
        margin: 30px auto;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    h2 {
        color: #1f2937;
        margin-top: 30px;
        border-left: 4px solid #2563eb;
        padding-left: 10px;
    }
    code, pre {
        background: #f3f4f6;
        padding: 10px;
        border-radius: 5px;
        font-size: 14px;
        display: block;
        margin: 10px 0;
        overflow-x: auto;
    }
    ul {
        padding-left: 20px;
        margin: 10px 0;
    }
    .tip {
        background: #e0f7fa;
        color: #00695c;
        padding: 10px;
        border-left: 4px solid #009688;
        margin-top: 10px;
        border-radius: 5px;
        font-size: 14px;
    }
    .contact-box {
        background: #f9fafb;
        border-left: 4px solid #2563eb;
        padding: 15px;
        margin-top: 20px;
        font-size: 15px;
    }
    footer {
        text-align: center;
        padding: 20px;
        font-size: 14px;
        color: #6b7280;
    }
</style>
</head>
<body>

<header>
    <h1>Laravel Sitemap Generator</h1>
    <p>A powerful tool to generate XML sitemaps with SSL support, deep crawling, and Laravel integration.</p>
</header>

<div class="container">

    <h2>🚀 Features</h2>
    <ul>
        <li>Crawl and generate a complete XML sitemap for your domain.</li>
        <li>Configurable maximum depth and URL limits.</li>
        <li>SSL verification using <code>cacert.pem</code>.</li>
        <li>Easy integration into Laravel projects.</li>
        <li>Logs for debugging and monitoring.</li>
    </ul>

    <h2>✅ Requirements</h2>
    <ul>
        <li>PHP 8.0+</li>
        <li>Laravel 9.x / 10.x</li>
        <li>Composer</li>
        <li>OpenSSL & cURL enabled</li>
    </ul>

    <h2>📦 Installation</h2>
    <ol>
        <li><strong>Clone the Repository</strong>
            <pre><code>git clone https://github.com/your-username/laravel-sitemap-generator.git
cd laravel-sitemap-generator</code></pre>
        </li>

        <li><strong>Install Dependencies</strong>
            <pre><code>composer install</code></pre>
        </li>
    </ol>

    <h2>🔒 SSL Configuration</h2>
    <p>The application requires SSL verification for secure HTTP requests.</p>
    <ol>
        <li>Download the latest <code>cacert.pem</code> file from <a href="https://curl.se/docs/caextract.html" target="_blank">cURL official site</a>.</li>
        <li>Create a directory:
            <pre><code>storage/app/certs/</code></pre>
        </li>
        <li>Place <code>cacert.pem</code> inside:
            <pre><code>storage/app/certs/cacert.pem</code></pre>
        </li>
    </ol>

    <h2>⚙️ Environment Setup (.env)</h2>
    <p>Add the following lines to your <code>.env</code> file:</p>
    <pre><code>HTTP_VERIFY=true
HTTP_CA_BUNDLE=D:\xampp_8_2_12\htdocs\abhi\site-map-generator\storage\app\certs\cacert.pem</code></pre>
    
    <div class="tip">Tip: Replace the path with your local or production environment path.</div>
    
    <p>If you want to disable SSL verification (not recommended), set:</p>
    <pre><code>HTTP_VERIFY=false</code></pre>

    <h2>▶️ Running the Application</h2>
    <ol>
        <li><strong>Generate Application Key (if new)</strong>
            <pre><code>php artisan key:generate</code></pre>
        </li>
        <li><strong>Start the Laravel Server</strong>
            <pre><code>php artisan serve</code></pre>
        </li>
    </ol>
    <p>Your app will be available at:</p>
    <pre><code>http://127.0.0.1:8000</code></pre>

    <h2>🛠 Usage</h2>
    <p><strong>Generating a Sitemap</strong></p>
    <ul>
        <li>Navigate to the application in your browser.</li>
        <li>Enter the base URL of the website you want to crawl.</li>
        <li>Click <strong>Generate Sitemap</strong>.</li>
        <li>The generated sitemap XML will be stored in:
            <pre><code>storage/app/sitemaps/</code></pre>
        </li>
    </ul>

    <h2>👨‍💻 Development Notes</h2>
    <ul>
        <li>Check Laravel logs for any errors:
            <pre><code>storage/logs/laravel.log</code></pre>
        </li>
        <li>Keep <code>APP_DEBUG=true</code> in <code>.env</code> during development.</li>
        <li>For production, set <code>APP_DEBUG=false</code>.</li>
    </ul>

    <h2>⚠️ Important</h2>
    <ul>
        <li>Ensure OpenSSL and cURL are enabled in <code>php.ini</code>.</li>
        <li>The <code>cacert.pem</code> file must be updated periodically for valid SSL verification.</li>
        <li>For large sites, increase:
            <ul>
                <li><code>max_execution_time</code></li>
                <li><code>memory_limit</code> in <code>php.ini</code></li>
            </ul>
        </li>
    </ul>

    <h2>📜 License</h2>
    <p>Open-sourced software licensed under the <a href="https://opensource.org/licenses/MIT" target="_blank">MIT License</a>.</p>

    <h2>👨‍💻 Contributing</h2>
    <ul>
        <li>Fork the repo</li>
        <li>Create a new branch for your feature/fix</li>
        <li>Submit a pull request</li>
    </ul>

    <h2>📧 Contact</h2>
    <div class="contact-box">
        <p><strong>Abhi Chavda</strong><br>
        Full Stack Developer<br>
        📞 <strong>+91 7016314980</strong><br>
        📧 <a href="mailto:abhichavda2004@gmail.com">abhichavda2004@gmail.com</a></p>
    </div>

</div>

<footer>
    &copy; 2025 Laravel Sitemap Generator. All rights reserved.
</footer>

</body>
</html>
