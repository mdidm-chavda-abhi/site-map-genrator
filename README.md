Laravel Sitemap Generator

Laravel Sitemap Generator is a simple yet powerful tool for generating XML sitemaps for your website. It supports SSL verification, deep crawling, and customizable settings for robust sitemap generation.

🚀 Features

Crawl and generate a complete XML sitemap for your domain.

Configurable maximum depth and URL limits.

SSL verification using cacert.pem.

Easy integration into Laravel projects.

Logs for debugging and monitoring.

✅ Requirements

PHP 8.0+

Laravel 9.x / 10.x

Composer

OpenSSL & cURL enabled

📦 Installation
1. Clone the Repository
git clone https://github.com/your-username/laravel-sitemap-generator.git
cd laravel-sitemap-generator

2. Install Dependencies
composer install

🔒 SSL Configuration

The application requires SSL verification for secure HTTP requests.

3. Download cacert.pem

Download the latest CA certificate bundle from cURL official site
.

Create a directory:

storage/app/certs/


Place cacert.pem inside:

storage/app/certs/cacert.pem

⚙️ Environment Setup

Add the following lines to your .env file:

HTTP_VERIFY=true
HTTP_CA_BUNDLE=D:\xampp_8_2_12\htdocs\abhi\site-map-generator\storage\app\certs\cacert.pem


Tip: Replace the path with your local or production environment path.

If you want to disable SSL verification (not recommended), set:

HTTP_VERIFY=false

▶️ Running the Application
4. Generate Application Key (if new)
php artisan key:generate

5. Start the Laravel Server
php artisan serve


Your app will be available at:

http://127.0.0.1:8000

🛠 Usage
Generating a Sitemap

Navigate to the application in your browser.

Enter the base URL of the website you want to crawl.

Click Generate Sitemap.

The generated sitemap XML will be stored in:

storage/app/sitemaps/


Download or view the sitemap from the interface.

👨‍💻 Development Notes

Check Laravel logs for any errors:

storage/logs/laravel.log


Keep APP_DEBUG=true in .env during development.

For production, make sure APP_DEBUG=false.

⚠️ Important

Ensure OpenSSL and cURL are enabled in php.ini.

The cacert.pem file must be updated periodically for valid SSL verification.

For large sites, increase:

max_execution_time

memory_limit in php.ini.

📜 License

Open-sourced software licensed under the MIT License
.

👨‍💻 Contributing

We welcome contributions!

Fork the repo

Create a new branch for your feature/fix

Submit a pull request

📧 Contact

For any queries or support, contact:

Abhi Chavda
Full Stack Developer
📞 +91 7016314980
📧 abhichavda2004@gmail.com
