# College-Attendance-Marks-Management-System

A full-stack web application that simplifies attendance tracking, marks management, and course material sharing for colleges. Built using PHP, MySQL, JavaScript, and HTML/CSS, the system supports three types of users: Admin, Teacher, and Student.

---

##  Features

###  Role-Based Dashboards
- **Admin**
  - Add and manage students and teachers
  - Assign usernames, passwords, divisions, and subjects
  - View/edit/delete marks and user accounts
- **Teacher**
  - Generate attendance session codes and QR links
  - Mark student attendance with location validation
  - Upload study materials (notes, assignments) filtered by subject and division
  - View, edit, and delete uploaded materials
  - View and manage student marks by subject and exam type
- **Student**
  - Mark attendance through shared link or code with real-time location check
  - View attendance report and marks
  - Access course materials by subject and division

---

##  Location-Based Attendance

- Integrated **HTML5 Geolocation API** to fetch student's latitude & longitude
- Used **Haversine formula** in PHP to calculate the distance between student and teacher
- Attendance allowed only if the student is within the specified range (e.g., 100 meters)
- Blocked attendance after multiple failed attempts from different devices or outside location

---

##  Course Material Sharing

- Teachers can upload files like notes, assignments, etc.
- Materials are associated with subject and division
- Only students of that specific division and subject can access/view/download materials
- Supports secure file upload with size/type validation

---

##  Security

- Passwords are hashed using `password_hash()` (Bcrypt)
- Role-based access control using PHP session checks
- File uploads validated for type and size to prevent abuse
- Session validation on every page using `redirectIfNotLoggedIn()` and `redirectIfNotRole()`

---

## Technologies Used

| Frontend         | Backend    | Database | Other APIs     |
|------------------|------------|----------|----------------|
| HTML, CSS, JS    | PHP        | MySQL    | Geolocation API|

---

##  Setup Instructions

1. **Clone the repo or download ZIP**
2. Place it in your `htdocs` folder (XAMPP)
3. Create a MySQL database and import the `database.sql` file
4. Update your database credentials in `includes/db.php`
5. Start Apache and MySQL using XAMPP
6. Access at `http://localhost/college-attendance-system`

---

## 📌 Author

**Ritik Mishra**  
_B.E Student | Web Developer_  
📧 Email: [ritikskmishra01@gmail.com]

---

## 📄 License

This project is for educational use. You are free to modify and use it with credits.

---




