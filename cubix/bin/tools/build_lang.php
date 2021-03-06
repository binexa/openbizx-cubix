<?php
/*
 * build module command line script
 */
if ($argc<2) {
	echo "usage: php build_lang.php lang_name".PHP_EOL;
	exit;
}

include_once ("../app_init.php");
if(!defined("CLI")){
	exit;
}

$langName = $argv[1];
$buildNumber = date("Ymd");
$ext = "tar.gz";

// invoke cubi/build/build mod_build.xml -Dbuild.module=$moduleName -Dbuild.number=$buildNumber
echo "---------------------------------------\n";
execPhing("lang_build.xml", "\"-DbuildName=$langName\" \"-DbuildNumber=$buildNumber\" \"-Dext=$ext\"");

function execPhing($buildFile, $options)
{
    $phingHome = OPENBIZ_APP_PATH.DIRECTORY_SEPARATOR."bin".DIRECTORY_SEPARATOR."phing";
    putenv("PHING_HOME=$phingHome");
    $phpClasses = $phingHome.DIRECTORY_SEPARATOR."classes";
    putenv("PHP_CLASSPATH=$phpClasses");
    $phingBin = $phingHome.DIRECTORY_SEPARATOR."bin";
    //putenv("PATH=$phingBin");
    $cmd = $phingBin.DIRECTORY_SEPARATOR."phing"." -buildfile $buildFile $options";
    echo "Executing $cmd\n";
    chdir(OPENBIZ_APP_PATH.DIRECTORY_SEPARATOR."build");
    system($cmd);
}


?>