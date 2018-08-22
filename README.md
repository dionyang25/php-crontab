# php-crontab
php秒级crontab实现

```php
    composer require dionyang/php-crontab
```

使用示例详见example\start.php

支持注册USR1 USR2信号

配置说明：

```php
'daemon' => '*/1 * * * * *' //date("s i H d m w")
```
