<?php
error_reporting(E_ALL);
ini_set("display_errors", "1");
class Search
{
    private $con;
    private $keyword;
    private $indexesShown = [];
    private $rec_shown = 0;
    private $rec_count = 10;
    private $pronouns = [
        ' i ',
        ' you ',
        ' he ',
        ' she ',
        ' it ',
        ' we ',
        ' you ',
        ' they ',
        ' mine ',
        ' yours ',
        ' his ',
        ' hers ',
        ' its ',
        ' ours ',
        ' yours ',
        ' theirs ',
        ' myself ',
        ' yourself ',
        ' himself ',
        ' herself ',
        ' itself ',
        ' ourselves ',
        ' yourselves ',
        ' themselves ',
        ' this ',
        ' that ',
        ' these ',
        ' those ',
        ' who ',
        ' whom ',
        ' whose ',
        ' which ',
        ' what ',
        ' who ',
        ' whom ',
        ' whose ',
        ' which ',
        ' that ',
        ' anybody ',
        ' anyone ',
        ' anything ',
        ' somebody ',
        ' someone ',
        ' something ',
        ' nobody ',
        ' no one ',
        ' nothing ',
        ' everybody ',
        ' everyone ',
        ' everything ',
        ' both ',
        ' few ',
        ' many ',
        ' several ',
        ' all ',
        ' any ',
        ' none ',
        ' some ',
        ' one ',
        ' another ',
        ' any ',
        ' each ',
        ' either ',
        ' neither ',
        ' several ',
        ' both ',
        ' many ',
        ' few ',
        ' some ',
        ' any ',
        ' several ',
        ' none ',
        ' each other ',
        ' one another'
    ];
    function __construct($host, $username, $password, $database)
    {
        $connect = mysqli_connect($host, $username, $password, $database);
        if (!$connect) {
            die("Unable to Connect");
        } else {
            $this->con = $connect;
        }
    }

    private function filterQuery($query)
    {
        return strtolower(htmlspecialchars($query));
    }
    private function removeUnwanted($text)
    {
        $res = strtolower(preg_replace("/[^\p{L}\p{N}\s]/u", "", $text));
        $res = " {$res} ";
        $res = str_replace($this->pronouns, "", $res);
        $res = trim($res);
        return $res;

    }

    public function setQuery($query)
    {
        $this->keyword = $query;
    }

    private function eachColumn($text, $sep)
    {
        $query = "title LIKE '%$text%' {$sep} description LIKE '%$text%' {$sep} heading LIKE '%$text%' {$sep} content LIKE '%$text%' {$sep} keywords LIKE '%$text%'";
        return $query;
    }

    private function filterAlreadyShown()
    {
        $qStr = "";
        foreach ($this->indexesShown as $id) {
            // phpcs:disable
            $qStr .= "$id, ";
            // phpcs:enable
        }
        $qStr = rtrim($qStr, ", ");
        return "IN [$qStr]";
    }

    private $sortByScoreAndAuthority = "ORDER BY (d.da+p.offscore+(1.5*p.score))/3 DESC";
    private $search_result = "p.id, p.title, p.description, p.url, d.name, d.favicon FROM page p INNER JOIN domain d ON p.domain = d.id";

    private function filterSortAndLimit()
    {
        $alreadyShown = $this->filterAlreadyShown();
        if ($alreadyShown === "IN []") {
            $string = "{$this->sortByScoreAndAuthority} LIMIT 0, 10";
        } else {
            $string = "AND NOT {$alreadyShown} {$this->sortByScoreAndAuthority} LIMIT 0, 10";

        }
        return $string;
    }
    private function indexToAlreadyShownAndReturn($query)
    {
        $result = [];
        while ($record = mysqli_fetch_assoc($query)) {
            array_push($result, $record);
            array_push($this->indexesShown, $record['id']);
        }
        return $result;
    }
    public function getByHeading()
    {
        $filterSortAndLimit = $this->filterSortAndLimit();
        $query = mysqli_query(
            $this->con,
            "SELECT {$this->search_result}
            WHERE heading LIKE '%{$this->keyword}%' {$filterSortAndLimit}; 
            "
        );
        return $this->indexToAlreadyShownAndReturn($query);
    }

    private function getByOtherHeadings()
    {
        $filterSortAndLimit = $this->filterSortAndLimit();
        $query = mysqli_query(
            $this->con,
            "SELECT {$this->search_result}
            WHERE hlevels LIKE '%{$this->keyword}%' {$filterSortAndLimit}
            "
        );
        return $this->indexToAlreadyShownAndReturn($query);
    }

    private function getInContent()
    {
        $filterSortAndLimit = $this->filterSortAndLimit();
        $query = mysqli_query(
            $this->con,
            "SELECT {$this->search_result}
            WHERE content LIKE '%{$this->keyword}%' {$filterSortAndLimit}
            "
        );
        return $this->indexToAlreadyShownAndReturn($query);
    }

    private function makeQuery($string, $separator)
    {
        $string = trim($string);
        $string = explode(" ", $string);
        // $string = join($string, )

    }


    /**
     * 
     * title LIKE '%fdgd%' AND title LIKE '%dfgdfdg%'
     */
    private $searchable_columns = ['title', 'description', 'keywords', 'heading', 'content', 'hlevels'];


    private function internalQuery($col, $sep, $query_array)
    {
        $string = "";
        foreach ($query_array as $word) {
            $string .= "$col LIKE '%$word%' $sep ";
        }
        $string = rtrim($string, " $sep ");
        return $string;
    }
    private function searchAllCols($int, $ext)
    {
        $keywords = $this->filterQuery($this->keyword);
        $keywords = $this->removeUnwanted($keywords);
        $keywords = explode(" ", $keywords);

        $string = "";
        foreach ($this->searchable_columns as $col) {
            $intQ = $this->internalQuery($col, $int, $keywords);
            $string .= "($intQ) $ext ";
        }
        $string = rtrim($string, " $ext ");
        return $string;
    }

    private function searchWordToExistInAllColumns()
    {
        $filterSortAndLimit = $this->filterSortAndLimit();
        $condition = $this->searchAllCols("AND", "AND");
        $query = mysqli_query($this->con, "
            SELECT {$this->search_result} WHERE {$condition} {$filterSortAndLimit}; 
        ");
        return $this->indexToAlreadyShownAndReturn($query);

    }
    private function searchAllWordsToExistInAnyColumn()
    {
        $filterSortAndLimit = $this->filterSortAndLimit();
        $condition = $this->searchAllCols("AND", "OR");
        $query = mysqli_query($this->con, "
            SELECT {$this->search_result} WHERE {$condition} {$filterSortAndLimit}; 
        ");
        return $this->indexToAlreadyShownAndReturn($query);

    }
    private function searchWordsToExistInAnyColDistributively()
    {
        $filterSortAndLimit = $this->filterSortAndLimit();
        $condition = $this->searchAllCols("OR", "AND");
        $query = mysqli_query($this->con, "
            SELECT {$this->search_result} WHERE {$condition} {$filterSortAndLimit}; 
        ");
        return $this->indexToAlreadyShownAndReturn($query);

    }
    private function weakFindInAnyCol()
    {
        $filterSortAndLimit = $this->filterSortAndLimit();
        $condition = $this->searchAllCols("OR", "OR");
        $query = mysqli_query($this->con, "
            SELECT {$this->search_result} WHERE {$condition} {$filterSortAndLimit}; 
        ");
        return $this->indexToAlreadyShownAndReturn($query);

    }
    public function search($page)
    {
        return [];
    }

    public function close()
    {
        mysqli_close($this->con);
    }

}
