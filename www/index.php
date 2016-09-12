<?php

include_once 'bootstrap.php';

if (!isset($_GET['track']) && !isset($track)) {
	if (file_exists('live.html')) {
		include 'live.html';
	} else {
		echo "No live template";
	}
} else {
	$_track = isset($_GET['track']) ? $_GET['track'] : '';
	$_track = $_track ?: (isset($track) ? $track : '');

	if (file_exists($_track.'.txt')) {
		echo file_get_contents($_track.'.txt');
	} else {
		echo "No track found";
	}
}
