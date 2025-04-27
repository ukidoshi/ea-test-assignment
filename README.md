Тестовое задание:
Логика интеграции с API реализована через команду fetch:api-data => [*FetchDataFromApiCommand*](https://github.com/ukidoshi/ea-test-assignment/blob/main/ea-test/app/Console/Commands/FetchDataFromApiCommand.php)

Реализация:
   - Постранично получаю записи по 500 записей каждую сущность(stocks, sales, incomes, orders).
   - Сохранение в базу данных сделаны через [очереди](https://github.com/ukidoshi/ea-test-assignment/blob/main/ea-test/app/Jobs/FetchPageDataJob.php).
<hr>

Панель управления БД [phpMyAdmin](https://vh301.spaceweb.ru/phpMyAdmin/):

логин: `ukidoonlin`

пароль: `@KMH2PCW2EJKBAAd`

<hr>

## Реквизиты БД MySQL:

# хост: 
```
VH301.spaceweb.ru
```
# порт:
```
3306
```
# база данных: 
```
ukidoonlin
```
# логин: 
```
ukidoonlin
```
# пароль: 
```
@KMH2PCW2EJKBAAd
```
<hr>

## Таблицы: ``sales, stocks, incomes, orders``
