-- Users Table
CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(200) NOT NULL,
    Full_Name VARCHAR(200) NOT NULL,
    BirthDate DATE,
    Gender ENUM('Male','Female','Other'),
    Email VARCHAR(200) UNIQUE NOT NULL,
    Username VARCHAR(200) UNIQUE NOT NULL,
    Password VARCHAR(200) NOT NULL,
    Phone_Number VARCHAR(200)
    City_or_Neighbourhood VARCHAR(200)
    Additional_Info VARCHAR(200)
); 

-- Admin Table
CREATE TABLE Admin (
    AdminID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(200) NOT NULL,
    Email VARCHAR(200) UNIQUE NOT NULL,
    Password VARCHAR(200) NOT NULL
);

-- Recycling Program Table
CREATE TABLE RecyclingProgram (
    ProgramID INT AUTO_INCREMENT PRIMARY KEY,
    ProgramName VARCHAR(200) NOT NULL,
    Description VARCHAR(200),
    Time DATETIME,
    Location VARCHAR(200),
    TypeOfRecycling VARCHAR(100),
    AdminID INT,
    FOREIGN KEY (AdminID) REFERENCES Admin(AdminID)
);

-- Energy Conservation Tips
CREATE TABLE EnergyConservationTips (
    TipsID INT AUTO_INCREMENT PRIMARY KEY,
    TipsTitle VARCHAR(200) NOT NULL,
    Category VARCHAR(200),
    Description VARCHAR(200),
    TimeCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    AdminID INT,
    FOREIGN KEY (AdminID) REFERENCES Admin(AdminID)
);

-- Gardening Project
CREATE TABLE GardeningProject (
    ProjectID INT AUTO_INCREMENT PRIMARY KEY,
    ProjectName VARCHAR(200) NOT NULL,
    Location VARCHAR(200),
    Time DATETIME,
    Description VARCHAR(200),
    AdminID INT,
    FOREIGN KEY (AdminID) REFERENCES Admin(AdminID)
);

-- Gardening Tips (Posts)
CREATE TABLE GardeningTips (
    PostID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(200) NOT NULL,
    Content VARCHAR(200),
    TimeCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    NumberOfLikes INT DEFAULT 0,
    NumberOfComments INT DEFAULT 0,
    NumberOfShares INT DEFAULT 0,
    UserID INT,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- Inbox
CREATE TABLE Inbox (
    InboxID INT AUTO_INCREMENT PRIMARY KEY,
    Subject VARCHAR(200)
);

-- Messages
CREATE TABLE Messages (
    MessageID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT,
    InboxID INT,
    Content TEXT,
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID),
    FOREIGN KEY (InboxID) REFERENCES Inbox(InboxID)
);

-- Item (Swap Items)
CREATE TABLE Item (
    ItemID INT AUTO_INCREMENT PRIMARY KEY,
    ProduceName VARCHAR(200),
    Category VARCHAR(200),
    ProduceType VARCHAR(200),
    Description VARCHAR(200),
    Status ENUM('Available','Exchanged','Removed') DEFAULT 'Available',
    UserID INT,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- Exchange
CREATE TABLE Exchange (
    ExchangeID INT AUTO_INCREMENT PRIMARY KEY,
    Status ENUM('Pending','Completed','Cancelled') DEFAULT 'Pending',
    Exchange_Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ExchangeDetailsID INT
);

-- ExchangeDetails
CREATE TABLE ExchangeDetails (
    ExchangeDetailsID INT AUTO_INCREMENT PRIMARY KEY,
    ItemID INT,
    UserID INT,
    FOREIGN KEY (ItemID) REFERENCES Item(ItemID),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- Ticket (For Programs/Projects)
CREATE TABLE Ticket (
    TicketID INT AUTO_INCREMENT PRIMARY KEY,
    Status ENUM('Active','Used','Cancelled') DEFAULT 'Active',
    TimeIssued TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UserID INT,
    ProgramID INT,
    ProjectID INT,
    FOREIGN KEY (UserID) REFERENCES Users(UserID),
    FOREIGN KEY (ProgramID) REFERENCES RecyclingProgram(ProgramID),
    FOREIGN KEY (ProjectID) REFERENCES GardeningProject(ProjectID)
);
