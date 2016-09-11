<?php

if (!isset($_GET['track'])) {
	if (file_exists('live.html')) {
		include 'live.html';
	} else {
		echo "No live template";
	}
} else {
	$track = $_GET['track'];
	if (file_exists($track.'.txt')) {
		echo file_get_contents($track.'.txt');
	} else {
		echo "No track found";
	}
}
