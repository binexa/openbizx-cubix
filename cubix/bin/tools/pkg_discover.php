#!/usr/bin/env php
<?php
/*
 * Cubi package discovery
 */

include_once ("../app_init.php");
if(!defined("CLI")){
	exit;
}

$packageService = "package.lib.PackageService";
// get package service 
$pkgsvc = Openbiz::getObject($packageService);

$categories = $pkgsvc->discoverCategories();
print_r($categories);

$packages = $pkgsvc->discoverPackages();
print_r($packages);
//$pkgsvc->downloadPackage('grm');

?>