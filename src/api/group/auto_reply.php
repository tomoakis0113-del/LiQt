<?php
require_once __DIR__ . "/../../vendor/autoload.php";

set_time_limit(30);

$schedules = models\ReplySchedule::query()
    ->select("id", "group_id", "chat_id")
    ->where("is_checking", "=", false)
    ->get();

foreach ($schedules as $schedule) {
    $schedule->is_checking = true;
    $schedule->save();

    try {
        $group_id = $schedule["group_id"];
        
        $context_array = models\Chat::where("group_id", "=", $group_id)
            ->select("id", "content", "sender_id")
            ->orderByDesc("id")
            ->limit(10)
            ->get()
            ->toArray();
        
        $context_array = array_reverse($context_array);

        $sender_ids = array_unique(array_column($context_array, 'sender_id'));
        $users = models\User::whereIn('id', $sender_ids)->get()->keyBy('id');

        $context = implode("\n", array_map(function($chat) use ($users) {
            $sender_name = isset($users[$chat["sender_id"]]) ? $users[$chat["sender_id"]]->name : "Unknown";
            return $sender_name . ": " . $chat["content"];
        }, $context_array));
        
        $ai = new lib\AI();
        $response = $ai->chat("以下のチャットに対して返信をしてください。\nai:についてはあなたが出したメッセージです。\n" . $context);

        models\Chat::create([
            "group_id" => $group_id,
            "sender_id" => $ai->getId(),
            "content" => $response,
        ]);

        $schedule->delete();

    } catch (\Exception $e) {
        error_log("Error processing schedule ID {$schedule->id}: " . $e->getMessage());
        
        $schedule->is_checking = false;
        $schedule->save();
    }
}