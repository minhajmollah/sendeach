<?php

namespace App\Jobs;

use App\Models\AiBot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\FineTunes\RetrieveResponse;

class TrainFineTuneModel implements ShouldQueue , ShouldBeUnique
{
    use Dispatchable , InteractsWithQueue , Queueable , SerializesModels;

    private AiBot $aiBot;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AiBot $aiBot)
    {
        $this->aiBot = $aiBot;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        [$tempFileLocation , $tempFile] = $this->formDataSet($this->aiBot->data['train_data']);

        $file = OpenAI::files()->list();
        $this->aiBot->data['files'] = array_map(fn($d) => $d->toArray() , $file->data);
        $this->aiBot->save();

        $file = OpenAI::files()->upload([
            'purpose' => $this->aiBot->name ,
            'file' => $tempFile
        ]);

        // Delete Temporary file after upload.
        unlink($tempFileLocation);

        // If Uploaded stop event streaming.
        if ($file->status == 'uploaded') {

            // Delete Previously Uploaded Files
            foreach (($this->aiBot->data['files'] ?? []) as $uploadedFile) {
                OpenAI::files()->delete($uploadedFile['id']);
            }

            // Save New File Response
            $this->aiBot->data['files'] = [$file->toArray()];
            $this->aiBot->save();

            // Delete Existing Model
//            $oldFineTuneModel = $this->aiBot->data['fine_tuned_model'] ?? null;
//            if ($oldFineTuneModel) {
//                try {
//                    $res = OpenAI::models()->delete($oldFineTuneModel);
//                    if ($res->deleted) {
//                        $this->aiBot->data['fine_tuned_model_status'] = 'pending';
//                        $this->aiBot->data['fine_tuned_model_stream_message'] = 'Deleted Previous Model';
//                        $this->aiBot->save();
//                    }
//                } catch (\Exception $exception) {
//                }
//            }


            // Create Fine Tune Data
            $fineTune = $this->createFineTune($file->id);

            // If not train success, stream train status and live update status to DB.
            if (!($fineTune->status == 'succeeded' || $fineTune->fineTunedModel)) {
                $this->streamUpdates($fineTune);
            }

        } else {
            $this->aiBot->data['fine_tuned_model_status'] = 'failed';
        }

        $this->aiBot->save();
    }

    private function formDataSet(array $dataSet)
    {
        $directory = sys_get_temp_dir();
        $prefix = 'tempfile_';
        $extension = '.jsonl';

        $tempFileWithExtension = tempnam($directory , $prefix) . $extension;

        $temp = fopen($tempFileWithExtension , 'wb+');

        foreach ($dataSet as $data) {
            fwrite($temp , json_encode($data) . "\n");
        }

        fseek($temp , 0);

        return [$tempFileWithExtension , $temp];
    }


    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return $this->aiBot->id;
    }

    public function streamUpdates(RetrieveResponse $fineTune): void
    {
        $stream = OpenAI::fineTunes()->listEventsStreamed($fineTune->id);

        // Get Training live status
        foreach ($stream as $streamResponse) {
            if (Arr::get($this->aiBot->data , 'fine_tuned_model_status') == 'cancelled') {
                $this->delete();
                break;
            }

            $this->aiBot->data['fine_tuned_model_stream_message'] = $streamResponse->message;
            $this->aiBot->save();
        }

        $fineTune = OpenAI::fineTunes()->retrieve($fineTune->id);

        $this->aiBot->data['fine_tuned_model'] = $fineTune->fineTunedModel;
        $this->aiBot->data['fine_tuned_model_status'] = $fineTune->status;
        $this->aiBot->save();
    }

    public function createFineTune(string $file): RetrieveResponse
    {
        $response = OpenAI::fineTunes()->create([
            'training_file' => $file ,
            'model' => $this->aiBot->model
        ]);

        $fineTune = OpenAI::fineTunes()->retrieve($response->id);

        $this->aiBot->data['fine_tune_id'] = $response->id;
        $this->aiBot->data['fine_tuned_model_status'] = $response->status;
        $this->aiBot->data['fine_tuned_model'] = $response->fineTunedModel;
        $this->aiBot->save();

        return $fineTune;
    }
}
