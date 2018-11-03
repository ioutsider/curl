# PHP Curl

## Usage

### Install

```bash
composer require 'luweiss/curl'
```

### Get

```php
require __DIR__ . '/vendor/autoload.php';

$curl = new \luweiss\Curl\Curl();
$response = $curl->get('http://a.b.com/test');
```

### Post

```php
$response = $curl->post('http://a.b.com/test', [
    'name' => 'Ben',
    'age' => 18,
]);
```

### Download file

```php
$fp = fopen(__DIR__ . '/filename', 'w');
$response = $curl->setOption(CURLOPT_FILE, $fp)->get($url);
```

### Upload file

```php
$file = '@/path/to/file';
$response = $curl->post('http://a.b.com/test', [
    'name' => 'Ben',
    'age' => 18,
    'file' => $file,
]);
```
