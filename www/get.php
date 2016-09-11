<?php

if (!isset($_GET['track'])) {
	include 'live.html';
} else {
	$track = $_GET['track'];
	if (file_exists($track.'.txt')) {
		echo file_get_contents($track.'.txt');
	}
}