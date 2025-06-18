<?php

class JsonFile
{
    private $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;

        $handle = $this->open();

        if (!$handle) {
            $this->createFile();
        }
    }

    public function read()
    {
        $handle = $this->open();

        $filse_size = $this->getFileSize();

        if ($filse_size > 0) {
            $content = fread($handle, $filse_size);
        } else {
            $content = json_encode([]);
        }

        $this->close($handle);

        return json_decode($content, true);
    }

    public function save($content)
    {
        $handle = $this->open(mode: 'w+');

        $content_encoded = json_encode($content);

        fwrite($handle, $content_encoded);

        $this->close($handle);
    }

    private function createFile()
    {
        $handle = $this->open(mode: 'w');
        $this->close($handle);
    }

    private function open($mode = 'r+')
    {
        return fopen(filename: $this->getFullFilename(), mode: $mode);
    }

    private function getFileSize()
    {
        return filesize($this->getFullFilename());
    }

    private function getFullFilename()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . $this->filename;
    }

    private function close($handle)
    {
        return fclose($handle);
    }
}

class Expense
{
    private $id;
    private $description;
    private $amount;
    private $createdAt;
    private $updatedAt;

    public function __construct($description, $amount)
    {
        $this->description = $description;
        $this->amount = $amount;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}

class Expenses
{
    private $file;

    public function __construct(JsonFile $file)
    {
        $this->file = $file;
    }

    public function add(Expense $expense)
    {
        $data = $this->file->read();

        $current_date = $this->getCurrentDate();

        $expense->setId($this->getNextId(data: $data));
        $expense->setCreatedAt(createdAt: $current_date);
        $expense->setUpdatedAt(updatedAt: $current_date);

        $data[] = [
            'id' => $expense->getId(),
            'description' => $expense->getDescription(),
            'amount' => $expense->getAmount(),
            'createdAt' => $expense->getCreatedAt(),
            'updatedAt' => $expense->getUpdatedAt(),
        ];

        $this->file->save($data);
    }

    private function getCurrentDate(): string
    {
        return date('Y-m-d H:i:s', strtotime('now'));
    }

    private function getNextId($data): int
    {
        if ($data) {
            $lastId = $data[array_key_last($data)]['id'];
        } else {
            $lastId = 0;
        }

        return ++$lastId;
    }
}

$options = "";
$longopts = [
    'add',
    'description:',
    'amount:'
];

$arguments = getopt(short_options: $options, long_options: $longopts);

if (count($arguments) > 0) {
    $feature = array_key_first($arguments);

    switch ($feature) {
        case 'add':
            $description = $arguments['description'];
            $amount = (float) $arguments['amount'];
            if (is_string($description) && $amount > 0) {
                $file = new JsonFile('expenses.json');
                $expense = new Expense($description, $amount);
                $expenses = new Expenses($file);
                $expenses->add( $expense);
            } else {
                echo "Please enter your arguments in the appropriate format.\n";
            }
            break;
        
        default:
            echo "The feature you entered is no valid\n";
            break;
    }
} else {
    echo "You need to set the arguments to continue\n";
}