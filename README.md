# 🗳️ Voting System

A **web-based online voting system** for colleges and small organizations, featuring user voting, admin management, and real-time results.

---

## ✨ Features

### 👤 User Features
- 🔐 **Authentication:** Secure login and registration, password hashing, session management  
- 🗳️ **Voting:** One vote per user, interactive candidate selection, confirmation messages  
- 📊 **Results:** Percentage-based progress bars, total vote counts, live results after voting  
- 🎨 **User Experience:** Responsive design, modern interface, visual feedback  

### 🛠️ Admin Features
- 📋 **Dashboard:** View real-time vote counts, candidate management  
- ➕➖ **Candidate Management:** Add/delete candidates, party selection  
- 🔄 **Vote Management:** Reset votes, update user voting status  
- 🛡️ **Security:** Session-based authentication, prepared statements, protected routes  

---

## 💻 Tech Stack

- **Backend:** PHP 7.x, MySQL, PHPMailer  
- **Frontend:** HTML5, CSS3, JavaScript, Font Awesome  
- **Dependencies:** PHPMailer, Composer  

---

## 🗄️ Database Structure

### 👥 Users Table


```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**🧑‍💼 Candidates Table**
```sql
-- Create candidates table
CREATE TABLE IF NOT EXISTS `candidates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `party` varchar(255) DEFAULT NULL,
  `votes` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample candidates (optional)
INSERT INTO `candidates` (`name`, `party`, `votes`) VALUES
('Candidate name', 'Party name', 0),
```

**🔑 Admin Table**
```sql
-- Create admin table
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin account
-- Username: admin
-- Password: admin123 (Change this after first login!)
INSERT INTO `admin` (`username`, `password`) VALUES
('admin', 'admin123');
```

---

## 📁 File Structure

```
voting-system/
├── config.php
├── login.php
├── register.php
├── logout.php
├── vote.php
├── composer.json
└── admin/
    ├── login.php
    ├── logout.php
    ├── dashboard.php
    ├── add_candidate.php
    └── reset_votes.php
```

---


## 🚀 Future Enhancements
- Password reset & email verification  
- Admin password hashing & security  
- Multiple elections & scheduling  
- Candidate profiles with photos  
- Real-time result updates via AJAX/WebSockets  
- Export results (CSV/PDF)  

---
## 👨‍💻 Author

**Sri Balakumar**

* GitHub: [@Sri-balakumar](https://github.com/Sri-balakumar)

## 📜 License

This project is licensed under the [MIT License](LICENSE).

---

⭐️ Star this repo if you found it useful!

````
