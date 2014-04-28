id3tagConvertor
===============

php convertor for ID3 tags

HELP:
php ./main.php
Error: Path to file is empty.
-d 	 - detect encoding ID3 tags action
-c 	 - convert encoding ID3 tags action
-f 	 - path to file
Example for detect: php ./main.php -d -f=/path/to/file.mp3
Example for fix: php ./main.php -c=cp1251 -f=/path/to/file.mp3


Смысл работы таков:
1.Вызов программы с параметром detect, например:
php ./main.php -d -f=/path/to/file.mp3
TITLE: '����� ��������'; ENC: 'default'
TITLE: 'Всего хорошего'; ENC: 'cp1251'

После вызова становится ясно что ID3tag в кодировке cp1251

2.Вызов программы с параметром convert, например:
php ./main.php -c=cp1251 -f=/path/to/file.mp3
И на выходе получим сохраненный исправленный файл. Допускается указывать каталог вместо файла
