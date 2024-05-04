<?php
if (isset($_GET['q']) && trim($_GET['q']) !== "") {
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $_GET['q']; ?> - FlexFind Search</title>
        <link rel="stylesheet" href="./css/main.css">
        <link rel="stylesheet" href="./css/search.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon">
    </head>

    <body>
        <header>
            <h1><a href="./" class="title-link">FlexFind</a></h1>
            <div>
                <form action="" method="get">
                    <input type="search" name="q" placeholder="Search Anything" value="<?php echo $_GET['q']; ?>" required>
                    <input type="submit" class="material-icons" value="search">
                </form>
            </div>
            <div class="info-btn">
                <a href="https://github.com/sinhapaurush" target="_blank">
                    <button class="material-icons" id="infobtn">info</button>
                </a>
            </div>
        </header>
        <main>
            <p class="search-span">Search Results for '<?php echo $_GET['q']; ?>'</p>
            <?php
            include_once ("./algo.php");
            $algo = new Search("localhost", "root", "", "search");
            $algo->setQuery($_GET['q']);
            // $result = $algo->search(1);
        

            $arr = [];
            function merge($temp)
            {
                global $arr;
                foreach ($temp as $value) {
                    array_push($arr, $value);
                }
            }
            merge($algo->searchInLink());
            merge($algo->searchWordToExistInAllColumns());
            merge($algo->getByHeading());
            merge($algo->getByOtherHeadings());
            merge($algo->getInContent());
            merge($algo->searchAllWordsToExistInAnyColumn());
            merge($algo->searchWordsToExistInAnyColDistributively());

            foreach ($arr as $result) {
                $title = trim($result["title"]);
                $title = $title === "" ? "No Title is Available" : $title;
                $url = $result["url"];
                $desc = trim($result['description']) === "" ? substr($result['content'], 0, 150) : $result['description'];
                $desc = "$desc...";
                $domain = $result["name"];
                $fav = $result["favicon"] === "none" ? "./favicon.ico" : $result["favicon"];
                echo <<<HTML
                <div class="result">
                    <div>
                        <img src="{$fav}" alt="{$domain} Favivon" class="favicon">    
                            <div class="domain-info">
                                <h3>{$domain}</h3>
                                <span>{$url}</span>
                            </div>
                    </div>
                    <a href="{$url}"><h2>{$title}</h2></a>
                    <p>{$desc}</p>
                </div>
    HTML;
            }

            $algo->close();
} else {
    header("Location: ./");
}
?>
    </main>
</body>

</html>