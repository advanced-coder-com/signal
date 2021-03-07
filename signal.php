<?php
// Установка параметров скрипта
set_time_limit(0);
ini_set("memory_limit", "500M");
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);


// Установка переменных
$email = 'mymail@mail.com';
$server = 'My local server';
$from = 'From: My Name <superAdmin@test.com>';
// Файлы исключения
$exceptions = array('.', '..', '/var/www/html/drupal-blog/cache');


// Если нужна другая директория, можем ее затать в GET параметре
if (isset($_GET['dir']) && $_GET['dir'] != ''){
	$dir = 	$_GET['dir'];
}else{
	$dir = $_SERVER['DOCUMENT_ROOT'];
}


// Проверка директории. Важно если это с GET параметра
if(!checkIfDir($dir)){
	die($dir.' Не является папкой');
}

// Получаем список файлов
$filesAll = getDirContents($dir, $exceptions);

// Отпрвляем отчет на почту
sendReport($filesAll, $email, $server, $from);



/*
 *  Функции	
 */



// Функция принимает путь к первой директории и возвращает все файлы, которые были изменены сегодня
function getDirContents($path, $exceptions) 
{

	$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    $files = array(); 

    foreach ($rii as $file){
    	$to_do = true;
    	foreach ($exceptions as $exception) {
    		if(substr($file, 0, strlen($exception)) === $exception){
    			$to_do = false;
    			break;
    		}
    	}
		if (!$file->isDir() && $to_do){
        	if (checkFileLastMod($file->getPathname())){
            	$files[] = $file->getPathname();
        	}
        }
    }
    return $files;
}


function checkIfDir($dir)
{
	return is_dir($dir);
}

// Функция проверяет дату последнего изменения файла и возвращает true, если файл изменен сегодня
function checkFileLastMod($filename)
{
	if($time = filemtime($filename)) {
		
		$today = date("m.d.y");  
		$mod_date = date("m.d.y", $time);


		// Более точный вариант
		 
		/* 
		$diff = time() - $time; // разница в секундах
		return ($diff/60/60) <= 24;
		*/

		return $mod_date == $today;
	}
}


function sendReport($filesAll, $to, $server, $from){
	$message = 'Отчет по активности на сервере '.$server.' за '.date('l jS \of F Y h:i:s A')."\r\n";
	if(count($filesAll) > 0) {
		foreach ($filesAll as $file) {
			$message .= 'Файл '.$file.' был изменен/добавлен '.date("Y-m-d H:i:s", filemtime($file))."\r\n";
		}
	}

	$subject = 'Отчет '.$server; 
	$headers =  $from. "\r\n";

	//file_put_contents('/var/www/html/signal_log', $message); // Если хотите посмотреть как это работает без отправки письма, данные можно сохранить в файл
	mail($to, $subject, $message, $headers);
}

echo "FINISH";



