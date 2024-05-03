<?php
include_once ("./search.php");



$algo = new Search("localhost", "root", "", "search");
$algo->setQuery("");
// $result = $algo->search(1);




print_r($algo->getByHeading());

$algo->close();