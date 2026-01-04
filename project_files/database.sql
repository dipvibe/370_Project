DROP DATABASE IF EXISTS house_hold_network;
CREATE DATABASE house_hold_network;
USE house_hold_network;

CREATE TABLE General_User (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    address VARCHAR(255),
    password VARCHAR(255) NOT NULL,
    role ENUM('worker','employer','administrator') NOT NULL,
    is_banned TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Worker (
    user_id INT PRIMARY KEY,
    experience INT DEFAULT 0,
    availability VARCHAR(100),
    phone VARCHAR(20),
    FOREIGN KEY (user_id) REFERENCES General_User(user_id) ON DELETE CASCADE
);

CREATE TABLE Employer (
    user_id INT PRIMARY KEY,
    phone VARCHAR(20),
    FOREIGN KEY (user_id) REFERENCES General_User(user_id) ON DELETE CASCADE
);

CREATE TABLE Administrator (
    user_id INT PRIMARY KEY,
    FOREIGN KEY (user_id) REFERENCES General_User(user_id) ON DELETE CASCADE
);

CREATE TABLE Worker_Skill (
    worker_id INT,
    skill VARCHAR(100),
    PRIMARY KEY (worker_id, skill),
    FOREIGN KEY (worker_id) REFERENCES Worker(user_id) ON DELETE CASCADE
);

CREATE TABLE Job_List (
    job_id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    area VARCHAR(100),
    schedule VARCHAR(100),
    salary_offer DECIMAL(10,2),
    house_no Varchar(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES Employer(user_id) ON DELETE CASCADE
);

CREATE TABLE Job_Work_Type (
    job_id INT,
    work_type VARCHAR(100),
    PRIMARY KEY (job_id, work_type),
    FOREIGN KEY (job_id) REFERENCES Job_List(job_id) ON DELETE CASCADE
);

CREATE TABLE Job_Request (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    worker_id INT NOT NULL,
    status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (job_id, worker_id),
    FOREIGN KEY (job_id) REFERENCES Job_List(job_id) ON DELETE CASCADE,
    FOREIGN KEY (worker_id) REFERENCES Worker(user_id) ON DELETE CASCADE
);

CREATE TABLE Hires (
    hire_id INT AUTO_INCREMENT PRIMARY KEY,
    worker_id INT NOT NULL,
    job_id INT NOT NULL UNIQUE,
    employer_id INT NOT NULL,
    joining_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (worker_id) REFERENCES Worker(user_id),
    FOREIGN KEY (job_id) REFERENCES Job_List(job_id),
    FOREIGN KEY (employer_id) REFERENCES Employer(user_id)
);

CREATE TABLE Payment_Record (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    hire_id INT NOT NULL,
    payment_month DATE,
    salary DECIMAL(10,2),
    employer_status ENUM('paid','unpaid') DEFAULT 'unpaid',
    worker_status ENUM('paid','unpaid') DEFAULT 'unpaid',
    pay_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hire_id) REFERENCES Hires(hire_id) ON DELETE CASCADE
);

CREATE TABLE Review (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    hire_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    reviewed_id INT NOT NULL,
    rating TINYINT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (hire_id, reviewer_id),
    FOREIGN KEY (hire_id) REFERENCES Hires(hire_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES General_User(user_id),
    FOREIGN KEY (reviewed_id) REFERENCES General_User(user_id)
);

CREATE TABLE Issue_Report (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    reported_user_id INT NOT NULL,
    hire_id INT,
    description TEXT NOT NULL,
    status ENUM('open','reviewed','resolved') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES General_User(user_id),
    FOREIGN KEY (reported_user_id) REFERENCES General_User(user_id),
    FOREIGN KEY (hire_id) REFERENCES Hires(hire_id)
);

