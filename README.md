# FlexFind Search Algorithm
This algorithm is used to get relavent search result from the Database as per the user query instead of just sorting the records by Id.

## Object Orientation
This algorithm is object oriented instead of procedural orientation. 

## How to use?

### Eligibility
- Database must be of db.sql Schema.

### Code
```php
$algorithm = new Search(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB);
$algortithm->setQuery(KEYWORD_TO_SEARCH);
$results = $algorithm->search(PAGE_NUMBER_OF_SEARCH_RESULT);

foreach ($results as $result){
    $url = $result['url'];
    $title = $result['title'];
    $desc = $result['description'];
    echo <<<HTML
        <a href="{$url}">{$title}</a>
        <p>$desc</p>
    HTML;
}
$algorithm->close();
```

Avadmin@12
root
search