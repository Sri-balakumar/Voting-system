# Voting System

A **web-based online voting system** for colleges and small organizations, featuring user voting, admin management, and real-time results.

---

## Features

### User Features
- **Authentication:** Secure login and registration, password hashing, session management  
- **Voting:** One vote per user, interactive candidate selection, confirmation messages  
- **Results:** Percentage-based progress bars, total vote counts, live results after voting  
- **User Experience:** Responsive design, modern interface, visual feedback  

### Admin Features
- **Dashboard:** View real-time vote counts, candidate management  
- **Candidate Management:** Add/delete candidates, party selection  
- **Vote Management:** Reset votes, update user voting status  
- **Security:** Session-based authentication, prepared statements, protected routes  

---

## Tech Stack

**Backend:** PHP 7.x, MySQL, PHPMailer  
**Frontend:** HTML5, CSS3, JavaScript, Font Awesome  
**Dependencies:** PHPMailer, Composer  

---

## Database Structure

**Users Table**
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    has_voted TINYINT(1) DEFAULT 0
);
```

**Candidates Table**
```sql
CREATE TABLE candidates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    party VARCHAR(255),
    votes INT DEFAULT 0
);
```

**Admin Table**
```sql
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
);
```

---

## File Structure

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

## Installation

1. Clone the repo:
   ```bash
   git clone [repository-url]
   cd voting-system
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Create MySQL database and tables (see Database Structure).  
4. Configure database in `config.php`  
5. Configure email SMTP in `register.php`  
6. Create admin account:
   ```sql
   INSERT INTO admin (username, password) VALUES ('admin', 'your_password');
   ```
7. Access:
   - User: `http://your-domain.com/`  
   - Admin: `http://your-domain.com/admin/`

---

## Future Enhancements
- Password reset & email verification  
- Admin password hashing & security  
- Multiple elections & scheduling  
- Candidate profiles with photos  
- Real-time result updates via AJAX/WebSockets  
- Export results (CSV/PDF)  

---

## License
**MIT License**  
**Author:** Sri Balakumar  
**Email:** sribalakumar04@gmail.com  
