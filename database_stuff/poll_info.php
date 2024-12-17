<?php
//Author: Keyu Wang
header('Content-Type: application/json');
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $db = new PDO('sqlite:ssDB.sq3'); 
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Validate poll_id
    if (isset($_GET['poll_id'])) {
        $pollId = $_GET['poll_id'];

        // Fetch poll details and related options using JOIN
        $stmt = $db->prepare("
            SELECT 
                mp.poll_id,
                mp.creator_id,
                mp.event_name,
                mp.url,
                mp.created_at,
                po.option_id,
                po.start_date,
                po.end_date,
                po.duration,
                po.start_time,
                po.end_time,
                po.vote_count
            FROM MeetingPolls mp
            LEFT JOIN PollOptions po ON mp.poll_id = po.poll_id
            WHERE mp.poll_id = ?
        ");
        $stmt->execute([$pollId]);

        // Fetch all rows
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$results) {
            echo json_encode(['success' => false, 'error' => 'Poll not found or no options available.']);
            exit;
        }

        // Format the response
        $poll = [
            'poll_id' => $results[0]['poll_id'],
            'creator_id' => $results[0]['creator_id'],
            'event_name' => $results[0]['event_name'],
            'url' => $results[0]['url'],
            'created_at' => $results[0]['created_at'],
        ];

        $options = array_map(function ($row) {
            return [
                'option_id' => $row['option_id'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'duration' => $row['duration'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'vote_count' => $row['vote_count'],
            ];
        }, $results);

        // Return JSON response
        echo json_encode([
            'success' => true,
            'poll' => $poll,
            'options' => $options,
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Poll ID not provided.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
