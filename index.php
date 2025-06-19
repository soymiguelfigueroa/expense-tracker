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

    public function __construct(Array $values)
    {
        $this->id = $values['id'] ?? null;
        $this->description = $values['description'] ?? null;
        $this->amount = $values['amount'] ?? null;
        $this->createdAt = $values['createdAt'] ?? null;
        $this->updatedAt = $values['updatedAt'] ?? null;
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

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
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

        return $expense;
    }

    public function getExpense($id)
    {
        $data = $this->file->read();

        foreach ($data as $value) {
            if ($value['id'] == $id) {
                return new Expense($value);
            }
        }

        return false;
    }

    public function update(Expense $expense)
    {
        $data = $this->file->read();

        foreach ($data as &$value) {
            if ($value['id'] == $expense->getId()) {
                $value['description'] = $expense->getDescription();
                $value['amount'] = $expense->getAmount();
                $value['updatedAt'] = $this->getCurrentDate();
            }
        }

        $this->file->save($data);
    }

    public function delete(Expense $expense)
    {
        $data = $this->file->read();

        foreach ($data as $key => $value) {
            if ($value['id'] == $expense->getId()) {
                unset($data[$key]);
                break;
            }
        }

        $this->file->save($data);
    }

    public function getList()
    {
        $data = $this->file->read();

        $list = [
            'metadata' => [
                'headers' => [
                    'Id' => [
                        'length' => strlen('Id'),
                    ],
                    'Description' => [
                        'length' => strlen('Description'),
                    ],
                    'Amount' => [
                        'length' => strlen('Amount'),
                    ],
                    'CreatedAt' => [
                        'length' => strlen('CreatedAt'),
                    ],
                    'UpdatedAt' => [
                        'length' => strlen('UpdatedAt'),
                    ],
                ]
            ],
            'data' => $data,
        ];

        foreach ($data as $key => $value) {
            if (strlen($value['id']) > $list['metadata']['headers']['Id']['length']) {
                $list['metadata']['headers']['Id']['length'] = strlen($value['id']);
            }
            if (strlen($value['description']) > $list['metadata']['headers']['Description']['length']) {
                $list['metadata']['headers']['Description']['length'] = strlen($value['description']);
            }
            if (strlen($value['amount']) > $list['metadata']['headers']['Amount']['length']) {
                $list['metadata']['headers']['Amount']['length'] = strlen($value['amount']);
            }
            if (strlen($value['createdAt']) > $list['metadata']['headers']['CreatedAt']['length']) {
                $list['metadata']['headers']['CreatedAt']['length'] = strlen($value['createdAt']);
            }
            if (strlen($value['updatedAt']) > $list['metadata']['headers']['UpdatedAt']['length']) {
                $list['metadata']['headers']['UpdatedAt']['length'] = strlen($value['updatedAt']);
            }
        }

        return $list;
    }

    public function getSumary($month)
    {
        $data = $this->file->read();

        $sumary = 0;

        foreach ($data as $value) {
            if ($month) {
                $monthOfValue = date(format: 'n', timestamp: strtotime($value['createdAt']));
                if ($monthOfValue == $month) {
                    $sumary += $value['amount'];
                }
            } else {
                $sumary += $value['amount'];
            }
        }

        return $sumary;
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
    'update',
    'delete',
    'list',
    'sumary',
    'id:',
    'description:',
    'amount:',
    'month:',
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
                $expense = new Expense([
                    'description' => $description, 
                    'amount' => $amount
                ]);
                $expenses = new Expenses($file);
                $expense = $expenses->add( $expense);

                echo "The expense has been saved successfully! (ID: {$expense->getId()})\n";
            } else {
                echo "Please enter your arguments in the appropriate format.\n";
            }
            break;

        case 'update':
            $id = (int) $arguments['id'];
            $description = $arguments['description'] ?? null;
            $amount = (float) $arguments['amount'] ?? null;
            if ($id > 0 && (is_string($description) || $amount > 0)) {
                $file = new JsonFile('expenses.json');
                $expenses = new Expenses($file);
                $expense = $expenses->getExpense($id);
                if ($description) {
                    $expense->setDescription($description);
                }
                if ($amount > 0) {
                    $expense->setAmount($amount);
                }
                $expenses->update($expense);
                    
                echo "The expense has been updated successfully!\n";
            } else {
                echo "Please enter your arguments in the appropriate format.\n";
            }
            break;

        case 'delete':
            $id = (int) $arguments['id'];
            if ($id) {
                $file = new JsonFile('expenses.json');
                $expenses = new Expenses($file);
                $expense = $expenses->getExpense($id);
                if ($expense) {
                    $expenses->delete($expense);

                    echo "The expense has been deleted successfully\n";
                } else {
                    echo "The expense could not be found\n";
                }
            } else {
                echo "Please enter the expense id.\n";
            }
            break;

        case 'list':
            $file = new JsonFile('expenses.json');
            $expenses = new Expenses($file);
            $list = $expenses->getList();
            $columns = array_keys($list['metadata']['headers']);
            $header = '';
            foreach ($columns as $column) {
                $header .= str_pad($column, $list['metadata']['headers'][$column]['length'] + 2);
            }
            echo "$header\n";
            foreach ($list['data'] as $row) {
                $data = '';
                foreach ($row as $columnKey => $column) {
                    $data .= str_pad($column, $list['metadata']['headers'][ucfirst($columnKey)]['length'] + 2);
                }
                echo "$data\n";
            }
            break;

        case 'sumary':
            $month = $arguments['month'] ?? null;
            if ($month !== null) {
                if ($month <= 0 || $month > 12) {
                    echo "You need to enter a valid month\n";
                    exit;
                }
            }
            $file = new JsonFile('expenses.json');
            $expenses = new Expenses($file);
            $sumary = $expenses->getSumary($month);
            if ($month !== null) {
                $monthName = date("F", mktime(0, 0, 0, $month, 10));
                echo "Total expenses for $monthName: $$sumary";
            } else {
                echo "Total expenses: $$sumary";
            }
            break;
        
        default:
            echo "The feature you entered is no valid\n";
            break;
    }
} else {
    echo "You need to set the arguments to continue\n";
}