## Coursly

This Symfony project requires a few steps before you can run it locally.

## Prerequisites

* PHP (>= 8.0)
* Composer
* Symfony CLI
* A database server (MySQL or MariaDB)
* phpMyAdmin (or any other database administration tool)

## Installation

1. **Create the database**
   Using phpMyAdmin (or another tool), create a database named `coursly`.

2. **Clone the repository and install dependencies**

   ```bash
   cd coursly
   composer install
   ```

3. **Update the database schema**

   ```bash
   php bin/console doctrine:schema:update --force
   ```

4. **Load the fixtures**

   ```bash
   php bin/console doctrine:fixtures:load --append
   ```

5. **Import the dummy data**
   In phpMyAdmin (or your preferred admin tool):

   * Select the `coursly` database.
   * Go to the **Import** tab.
   * Click **Browse**, choose the `dummy_data.sql` file.
   * Leave the default options and click **Go**.

6. **Start the development server**

   ```bash
   symfony serve
   ```

The project will now be accessible at the address shown by the Symfony CLI (by default: [http://127.0.0.1:8000](http://127.0.0.1:8000)).

## Administrator Login

The first user created is the administrator:

* **Email:** `admin@coursly.com`
* **Password:** `admincoursly`

Use these credentials to log in and access the admin area.

## Screenshots

Below are some example screenshots to help you get started.
![Screenshot 2025-06-09 at 23-20-44 My Courses](https://github.com/user-attachments/assets/ec22fb4f-230a-49d5-b2cd-79d15ea6fb23)
![Screenshot 2025-06-09 at 23-15-33 Home](https://github.com/user-attachments/assets/7b67eee6-9243-4c29-afb5-52ee42ffdb31)
![Screenshot 2025-06-09 at 23-15-28 announcement list title](https://github.com/user-attachments/assets/b3356a8d-ee33-43eb-9f1a-b9ceb80c28e5)
![Screenshot 2025-06-09 at 23-14-20 Users](https://github.com/user-attachments/assets/c53d2e07-4911-43aa-8844-9f2addff3764)
![Screenshot 2025-06-09 at 23-12-35 Manage Enrollments for Joe Filler](https://github.com/user-attachments/assets/38b52a53-1d30-498d-8307-a00884236cf1)



