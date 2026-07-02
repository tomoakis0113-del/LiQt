<?php
require_once __DIR__ . "/../vendor/autoload.php";

$notices = models\NoticeSchedule::all(["id", "user_id", "content"]);
foreach ($notices as $notice) {
    try {
        $notice->is_checking = true;
        $notice->save();

        $user = models\User::find($notice->user_id);
        if (!$user) {
            throw new Exception("User not found for ID: {$notice->user_id}");
        }
        
        lib\SendMail::send($user->mail_address, "お知らせ", $notice->content);
        $notice->delete();

    } catch (Exception $e) {
        error_log("Failed to process notice for user_id: " . ($notice->user_id ?? 'unknown') . ". Error: " . $e->getMessage());
        try {
            $notice->is_checking = false;
            $notice->save();
        } catch (Exception $saveEx) {
            error_log("Failed to revert is_checking flag: " . $saveEx->getMessage());
        }
    }
}