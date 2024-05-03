<?php
include_once ("./search.php");



$algo = new Search("localhost", "root", "", "search");
$algo->setQuery("how are you");
$result = $algo->search(1);




// print_r($db->getByHeading());

$db->close();