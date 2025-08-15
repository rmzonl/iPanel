<p align="center">
<a href="https://packagist.org/packages/znframework/package-compression" rel="nofollow">
	<img src="https://img.shields.io/packagist/dt/znframework/package-compression?style=flat-square" style="max-width:100%;"></a>
<a href="//packagist.org/packages/znframework/package-compression" rel="nofollow">
	<img src="https://img.shields.io/github/v/release/znframework/package-compression?style=flat-square&color=00BFFF" style="max-width:100%;"></a>
<a href="//packagist.org/packages/znframework/package-compression" rel="nofollow">
	<img src="https://img.shields.io/github/release-date/znframework/package-compression?style=flat-square" style="max-width:100%;"></a>
<a href="//packagist.org/packages/znframework/package-compression" rel="nofollow">
	<img src="https://img.shields.io/github/license/znframework/package-compression?style=flat-square" style="max-width:100%;"></a>
</p>

<h2>ZN Framework Compression Package</h2>
<p>
Follow the steps below for installation and use.
</p>

<h3>Installation</h3>
<p>
You only need to run the following code for the installation.
</p>

```
composer require znframework/package-compression
```

<h3>Documentation</h3>
<p>
Click for <a href="https://docs.znframework.com/veri-bicimlendirme-kutuphaneleri/sikistirma-kutuphanesi">documentation</a> of your library.
</p>

<h3>Example Usage</h3>
<p>
Basic level usage is shown below.
</p>

```php
<?php require 'vendor/autoload.php';

ZN\ZN::run();

$data = Compress::do('Example Data');

echo Compress::undo($data);
```
