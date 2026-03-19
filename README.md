# 🚆 Train Management System

A web-based Train Management System developed using **PHP, MySQL,  CSS, and JavaScript**.
This project allows users to search trains, book tickets, and manage railway operations efficiently.

---

## 📌 Features

### 👤 User Features

* 🔍 Search trains between stations
* 🎟️ Book train tickets
* 🧾 View booking details
* 🔐 User registration & login system

### 🛠️ Admin Features

* ➕ Add / manage trains
* 🗂️ Manage routes and stations
* 📊 View reports
* 💳 Manage payments

---

## 🏗️ Tech Stack

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP
* **Database:** MySQL
* **Server:** XAMPP / InfinityFree

---

## 📁 Project Structure

```
train-management-system/
│
├── admin/             # Admin panel
├── api/               # Backend APIs
├── assets/            # CSS, JS, images
├── booking/           # Booking module
├── config/            # Database configuration
├── includes/          # Common files (header/footer)
├── payments/          # Payment handling
├── reports/           # Reports module
├── routes/            # Route management
├── stations/          # Station data
├── trains/            # Train data
├── uploads/           # Uploaded files
│
├── index.php          # Homepage
├── login.php          # Login page
├── register.php       # Register page
└── logout.php         # Logout
```

---

## ⚙️ Installation & Setup

### 🔹 1. Clone the repository

```
git clone https://github.com/your-username/train-management-system.git
```

---

### 🔹 2. Move to XAMPP folder

```
C:\xampp\htdocs\
```

---

### 🔹 3. Start server

* Open XAMPP
* Start **Apache** and **MySQL**

---

### 🔹 4. Import Database

1. Open **phpMyAdmin**
2. Create a database
3. Import the file:

```
train_management_system.sql
```

---

### 🔹 5. Configure Database

Update database connection in:

```
config/db.php
```

Example:

```php
$conn = mysqli_connect("localhost", "root", "", "your_database_name");
```

---

### 🔹 6. Run the project

Open browser:

```
http://localhost/train-management-system
```

---

## 🌐 Live Demo

👉 https://trainmanagement.free.nf

---

## ⚠️ Notes

* Make sure MySQL is running
* Update DB credentials before running
* Do not upload sensitive data (passwords, keys)

---

## 🤝 Contributing

Contributions are welcome!
Feel free to fork the repo and submit a pull request.

---

## 📄 License

This project is for educational purposes.

---

## 👨‍💻 Author

**Prashant Kumar and Manish Ghatuary**


---

⭐ If you like this project, don’t forget to star the repository!
