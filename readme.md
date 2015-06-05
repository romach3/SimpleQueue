# SimpleQueue

## Установка

* Клонируйте репозиторий и выполните ``composer install``
* Запустите команду ``./queue listen TUBE_NAME`` в фоне.

## Окружение

### Beanstalkd

``sudo apt-get install beanstalkd``

Сервер очередей, во круг которого всё и крутится.

[http://kr.github.io/beanstalkd/](beanstalkd) - сервер

[https://github.com/pda/pheanstalk](pda/pheanstalk) - php-пакет для работы с ним

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

[http://supervisord.org/](supervisord.org)

## Использование


### Добавление задачи

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
