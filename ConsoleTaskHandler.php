<?php

class ConsoleTaskHandler
{
    protected $separator;
    protected $command;
    protected $file_with_users;

    function __construct(string $separator, string $command)
    {
        $this->separator = $separator;
        $this->command = $command;
        $this->file_with_users = fopen("people.csv", "r+") ?: null;
    }

    public function __destruct()
    {
        fclose($this->file_with_users);
    }

    public function getResult()
    {
        if ($this->file_with_users == null) {
            return print_r("Файла 'people.csv' не найдено в каталоге");
        }

        $users = $this->getUsers();
        if (count($users) == 0) {
            return print_r("В файле 'people.csv' не найдено пользователей");
        }

        return $this->commandProcessing($users);
    }

    protected function getUsers()
    {
        $users = [];
        switch ($this->separator) {
            case 'comma':
            {
                while (($data = fgetcsv($this->file_with_users, null, ',')) !== false) {
                    $users[] = $data;
                }
                break;
            }
            case 'semicolon':
            {
                while (($data = fgetcsv($this->file_with_users, null, ";")) !== false) {
                    $users[] = $data;
                }
                break;
            }
            default:
                return print_r("Сепаратор не распознан");
        }
        return $users;
    }

    protected function commandProcessing(array $users)
    {
        for ($i = 0; $i < count($users); $i++){

            if(!is_numeric($users[$i][0])) {
                return print_r(
                    "Не удалось получить id пользователя в файле. Проверьте правильность написания команды,".
                    "вот что мы получили вместо Id: ".$users[$i][0]."\n"
                );
            }

            switch ($this->command){
                case "countAverageLineCount":
                {
                    $avg = $this->countAverageLineCountForUser($users[$i][0]);
                    print_r($avg." - среднее кол-во строк в файлах пользователя ".$users[$i][1]."\n");
                    break;
                }
                case "replaceDates":
                {
                    $countDates = $this->replaceDatesForUser($users[$i][0]);
                    print_r($countDates." - кол-во замен дат в файлах пользователя ".$users[$i][1]."\n");
                    break;
                }
                default:
                {
                    return print_r("Неизвестный тип задачи");
                }
            }
        }
        return true;
    }

    protected function countAverageLineCountForUser(int $userId)
    {
        $files = scandir("texts");
        $userFilesCount = 0;
        $countStringInUserFiles = 0;
        foreach ($files as $fileName) {
            $userIdInFile = explode('-', $fileName)[0];
            if ($userIdInFile == $userId) {
                $userFilesCount++;
                $countStringInUserFiles += count(file('texts/'.$fileName));//Подсчет строк в файле
            }
        }
        if ($userFilesCount !== 0) {
            return $countStringInUserFiles / $userFilesCount;
        }
        return 0;
    }

    protected function replaceDatesForUser(int $userId)
    {
        $files = scandir("texts");
        unset($files[0], $files[1]);//Удаляем пути "." и ".."
        $countAllReplace = 0;
        foreach ($files as $fileName) {
            $userIdInFile = explode('-', $fileName)[0];
            if ($userIdInFile == $userId) {
                $res = preg_replace(['/([0-3][\d])\/([01][0-12])\/([0-4]\d)/','/([0-3][\d])\/([01][0-12])\/([5-9]\d)/'],
                                    ['${2}-${1}-20${3}','${2}-${1}-19${3}'],
                                    file('texts/'.$fileName), -1, $countReplaceInFile);
                $this->writeResultFile($fileName, implode("", $res));
                $countAllReplace += $countReplaceInFile;
            }
        }
        return $countAllReplace;
    }

    protected function writeResultFile(string $fileName, string $text){
        if(!is_dir("output_texts")){
            mkdir("output_texts");
        }
        $fileStream = fopen("output_texts/".$fileName, "w+");
        fwrite($fileStream, $text);
        fclose($fileStream);
    }
}