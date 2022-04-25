# About

This project shows how to retrieve and show dates based on local time, while saving them normalized as UTC in the database.

Refer to the **index.php** file/page to find the main extracts of code and explanations on the two possible ways to achieve this purpose:
- Normalizing dates to UTC with aid of the js datetime library 'dayjs'.
- Normalizing dates to UTC without use of any external JS datetime library.


# Installation

1. Download the project from the repository.

```bash
git clone https://github.com/gaadeveloper/php-normalizing-dates-to-utc.git
```

2. Create a new database on your favorite database administration tool, and create the table 'dates' in this newly-created database running the following DDL code on a new SQL script:

```
-- public.dates definition
-- Drop table
-- DROP TABLE public.dates;
CREATE TABLE public.dates (
	date timestamptz NULL
);
```

3. Create a file DbEnv.php inside the 'public' folder, paste the following code and change the environment data to the right database connection data in your case.

```
<?php

class DbEnv {
    public const HOST = 'postgres';
    public const DB = 'your_db_name';
    public const USER = 'your_user_name';
    public const PASSWORD = 'your_password_name';
}
````
