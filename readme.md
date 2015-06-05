# SimpleQueue

## Установка

* Клонируйте репозиторий и выполните ``composer install``
* Запустите команду ``./queue listen TUBE_NAME`` в фоне.

## Окружение

### Beanstalkd

``sudo apt-get install beanstalkd``

Сервер очередей, вокруг которого всё и крутится.

[beanstalkd](http://kr.github.io/beanstalkd/) - сервер

[pda/pheanstalk](https://github.com/pda/pheanstalk) - php-пакет для работы с ним

### Supervisor
 
``sudo apt-get install supervisor``
 
Пригодится для поддержания queue:listen в запущенном состоянии (можно и без него, но так будет гарантия, что
ничего не отвалится от долгой работы).

Пример конфига:

```
[program:simple]
directory=/sites/SimpleQueue
autorestart=true
redirect_stderr=true
command=./queue listen tube
stdout_logfile=/sites/SimpleQueue/App/Logs/listen.log
stdout_logfile_maxbytes=50MB
stdout_logfile_backups=50
stdout_capture_maxbytes=1MB
```

Конфиги складываются в каталог /etc/supervisor/conf.d

После их обновления необходимо выполнить команды:

* ``supervisorctl update`` что бы перечитать конфигурацию.
* ``supervisorctl restart simple``

[supervisord.org](http://supervisord.org/)

## Использование

### Структура проекта

* App - тут должны находиться файлы приложения: задачи и то что их стартует, передавая необходимые данные.
* App/Jobs - каталог для задач
* App/Logs - логи выполнения задач
* Kernel - файлы SimpleQueue
* public - добавление задач через HTTP.

### Добавление задачи через PHP

Просто создайте тем или иным путем новый экземпляр класса Push, передав в конструктор параметры.

```
use Kernel\Queue\Push;
new Push('SimpleQueueTube', 'SimpleJob', 'Yeah!');
```

Добавляет задачу в очередь SimpleQueueTube. При выполнени создается объект класса SimpleJob
и ему будут переданы данные "Yeah!". В качестве данных может быть передано все что поддается сериализации средствами PHP.

Прим.: следует учитывать, не все что сериализуется можно сериализовать. К примеру модель в ORM, которая хранит в себе
текущее состояние записи в БД: ко времени выполнения задачи оно возможно уже изменится.

Прим.: по умолчанию, если имя класса не начинается с \, подставляется неймспейс \App\Jobs.

### Добавление задачи через HTTP

Для начала необходимо настроить ваш сервер так, что бы ``public`` стал корневым каталогом сайта.

Затем необходимо разрешить задачи в файле настроек ``App/http.php``:

```
<?php return [
    'jobs' => [
        'simple-job' => [ // Название задачи
            'class' => 'SimpleJob', // Класс задачи
            'parameters' => true, // Принимать ли параметры
            'data' => 'Yeah!', // Параметры передаваемые в задачу
        ]
    ],
    'tubes' => ['SimpleQueueTube'] // Список разрешенных очередей,
    'auth' => function() { // Любая форма авторизации
            return false; // Добавление задачи запрещено.
        }
];
```

Затем задачу можно будет добавить в очередь выполнив запрос по адресу:

``http://example.com/?tube=SimpleQueueTube&job=simple-job&another_data=value``

``tube`` и ``job`` указывают на название задачи и очереди, всё остальное уйдет в данные, если это разрешено (``parameters``).

Прим.: входящий запрос проверяется только на разрешенность для выполнения очереди и задачи!

Прим.: если ``parameters ===  false``, то данные из ``data`` будут переданы в том виде что есть, во всех остальных
случаях в задачу будет передан массив. Соответственно, если ``parameters ===  true``, а ``data`` массивом не является,
то она будет в него преобразована методом ``$data = [$data]``, что бы объеденить с данными из запроса и станет нулевым
элементом.

Прим.: ``auth`` может быть чем угодно, важно что бы функция вернула true/false.

### Задачи

Все задачи должны наследоваться от ``Kernel\Queue\JobAbstract`` и при возможности распологаться по неймспейсу
(и пути для удобства) \App\Jobs. 
Данные переданные при добавлении задачи будут находится в свойстве объекта ``data``,
метод ``$this->log('string or array strings')`` добавляет записи в лог очереди.

Сама же задача стартуется методом ``->start()``.

```
<?php namespace App\Jobs;
   
use Kernel\Queue\JobAbstract;
   
class SimpleJob extends JobAbstract {

    public function start() {
        $this->log($this->data);
    }

}
```

### Логи

Логи находятся в каталоге ``App/Logs``. 

## Проверка работы

Запустите queue:listen и выполните команду ``./queue test``, она стартует задачу,
которая добавит в лог ``App/Logs/SimpleQueueTube.log`` строку "Yeah!".
