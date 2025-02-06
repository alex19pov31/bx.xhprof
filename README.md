# XHprof for bitrix

Установка:

```shell
composer require alex19pov31/bx.xhprof
```

Примеры использования:

```php
use Bx\XHProf\XHProfManager;

XHProfManager::instance()->start();             // запускаем профайлер
//some code ....
XHProfManager::instance()->end('custom_label'); //  останавливаем профайлер и записываем данные
```

Описание условий для включения профайлера:

```php
use Bx\XHProf\BaseChecker;
use Bx\XHProf\DefaultChecker;
use Bx\XHProf\XHProfManager;

class CustomChecker extends BaseChecker 
{
    protected function check(): bool
    {
        if ([some_logic...]) {
            return true;
        }
        
        return false;
    }
}

XHProfManager::instance()->setStrategy(
    new CustomChecker(new DefaultChecker())
);
```
