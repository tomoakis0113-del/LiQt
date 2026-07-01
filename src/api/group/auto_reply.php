<?php
require_once __DIR__ . "/../../vendor/autoload.php";

set_time_limit(300);

$schedules = models\ReplySchedule::query()
    ->select("id", "blog_id", "group_id", "type")
    ->where("is_checking", "=", false)
    ->get();

foreach ($schedules as $schedule) {
    $schedule->is_checking = true;
    $schedule->save();

    try {
        // チャットの返信スケジュールの場合
        if($schedule["type"] === "chat"){
            $group_id = $schedule["group_id"]??null;
            if (!$group_id) {
                throw new Exception("Group ID is missing for schedule ID: {$schedule->id}");
            }

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
        }
        // ブログの返信スケジュールの場合
        else if($schedule["type"] === "blog"){
            $blog_id = $schedule["blog_id"] ?? null;
            if (!$blog_id) {
                throw new Exception("Blog ID is missing for schedule ID: {$schedule->id}");
            }

            $blog = models\Blog::where("id", "=", $blog_id)->first(["title", "content", "tags"]);
            if (!$blog) {
                throw new Exception("Blog not found for ID: {$blog_id}");
            }

            $ai = new lib\AI();
            $response = $ai->chat("以下のブログに対して要約して感想を述べてください。\n**Markdownでなく通常のテキストで返信してください。**" . "title:{$blog->title}\ncontent:{$blog->content}\ntags:{$blog->tags}");

            models\BlogComment::create([
                "blog_id" => $blog_id,
                "user_id" => $ai->getId(),
                "content" => $response,
            ]);
        }

    } catch (\Exception $e) {
        error_log("Error processing schedule ID {$schedule->id}: " . $e->getMessage());

    } finally {
        $schedule->delete();
    }
}