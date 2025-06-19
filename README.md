# Expense tracker

This project is a CLI app to manage your finances.

## Project URL

[Roadmap Expense tracker](https://roadmap.sh/projects/expense-tracker)

## Requirements

You will need PHP (V8 or higher) to use this CLI app. I reccomend you XAMPP to use this app.

## Usage
The list of commands and their usage is given below:

### Adding a new expense
```
php index.php --add --description "Lunch" --amount 20
```

### Updating a expense
```
php index.php --update --id 1 --description "breakfast" --amount 40
```

### Deleting a expense
```
php index.php --delete --id 3
```

### View all expenses
```
php index.php --list
```

### View a summary of all expenses
```
php index.php --sumary
```

### View a summary of all expenses for a specific month
```
php index.php --sumary --month 6
```