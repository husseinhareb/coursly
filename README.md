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
