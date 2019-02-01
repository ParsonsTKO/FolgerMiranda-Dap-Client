<?php

namespace DAPClientBundle\Services;

class DownloadService
{
    /**
     * @var array
     */
    public $downloadSettings;
    
    public function __construct(array $downloadSettings)
    {
        $this->downloadSettings = $downloadSettings;
    }

    public function generateCsvFromRecords($records)
    {
        $csvArray = [
            $this->getCsvFields(),
        ];
           
        foreach ($records as $record) {
            $csvArray[] = $this->getRecordCsvFields($record);
        }
                        
        return $this->getCsvContent($csvArray);
    }

    public function getRecordCsvFields($record)
    {
        $record = json_decode(json_encode($record), false);
        return [
            isset($record->title->displayTitle) ? $record->title->displayTitle : '',
            isset($record->creator) ? $record->creator : '',
            isset($record->dateCreated)? json_encode($record->dateCreated) : '',
            isset($record->locationCreated)? json_encode($record->locationCreated) : '',
            isset($record->format)? json_encode($record->format) : '',
            isset($record->extent)? $record->extent : '',
            isset($record->language)? json_encode($record->language) : '',
            isset($record->holdingInstitution)? json_encode($record->holdingInstitution) : '',
            isset($record->license)? $record->license : '',
            isset($record->size)? $record->size : '',
            isset($record->mirandaGenre)? json_encode($record->mirandaGenre) : '',
            isset($record->genre)? json_encode($record->genre) : '',
            isset($record->identifiers)? json_encode($record->identifiers) : '',
            isset($record->abstract)? $record->abstract : '',
            isset($record->notes)? json_encode($record->notes) : '',
            isset($record->title->alternateTitles)? json_encode($record->title->alternateTitles) : '',
            isset($record->title->uniformTitle)? json_encode($record->title->uniformTitle) : '',
            isset($record->subjects)? json_encode($record->subjects) : '',
            isset($record->relationships->agents)? json_encode($record->relationships->agents) : '',
            isset($record->relationships->works)? json_encode($record->relationships->works) : '',
            isset($record->folgerRelatedItems)? json_encode($record->folgerRelatedItems) : '',
            isset($record->relationships->locations)? json_encode($record->relationships->locations) : '',
            isset($record->preferredCitation)? json_encode($record->preferredCitation) : '',
            isset($record->groupings)? json_encode($record->groupings) : '',
            isset($record->simplifiedTranscription)? $record->simplifiedTranscription : '',
            isset($record->fileInfo)? json_encode($record->fileInfo) : '',
        ];
    }

    public function getCsvContent($data)
    {
        $handle = fopen('php://temp', 'w');

        foreach ($data as $lines) {
            fputcsv($handle, $lines);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    public function getCsvFields()
    {
        return $this->downloadSettings["csv_fields"];
    }

    public function getCsvFilenameFromRecord($record)
    {
        return (array_key_exists("displayTitle", $record["title"]) && !empty($record["title"]["displayTitle"]))? $record["title"]["displayTitle"] : $record["dapID"];
    }
}
