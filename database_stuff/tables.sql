-- Create Users Table
CREATE TABLE Users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT, -- Primary Key
    fname TEXT NOT NULL,
    lname TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL, -- Placeholder for storing passwords
    --role TEXT CHECK (role IN ('professor', 'TA', 'student', 'member')) DEFAULT 'member', -- CHECK THIS LATER
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Events Table
CREATE TABLE Events (
    slot_id INTEGER PRIMARY KEY AUTOINCREMENT, -- Primary Key
    creator_id INTEGER NOT NULL, -- Foreign Key referencing Users(user_id)
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    duration INTEGER NOT NULL, -- Duration in minutes
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    event_name TEXT NOT NULL,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    location TEXT,
    max_people INTEGER,
    url TEXT,
    FOREIGN KEY (creator_id) REFERENCES Users(user_id)
);

-- Create Bookings Table
CREATE TABLE Bookings (
    booking_id INTEGER PRIMARY KEY AUTOINCREMENT, -- Primary Key
    slot_id INTEGER NOT NULL, -- Foreign Key referencing Events(slot_id)
    booked_by_id INTEGER, -- Foreign Key referencing Users(user_id)
    booked_by_email TEXT NOT NULL, -- Email of the person who booked the slot
    num_people INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (slot_id) REFERENCES Events(slot_id) ON DELETE CASCADE,
    FOREIGN KEY (booked_by_id) REFERENCES Users(user_id)
);

-- Create Time Requests Table
CREATE TABLE TimeRequests (
    request_id INTEGER PRIMARY KEY AUTOINCREMENT, -- Primary Key
    sender_id INTEGER NOT NULL, -- Foreign Key referencing Users(user_id)
    receiver_id INTEGER NOT NULL, -- Foreign Key referencing Users(user_id)
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    duration INTEGER NOT NULL, -- Duration in minutes
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    description TEXT,
    eventName TEXT NOT NULL,
    status TEXT CHECK (status IN ('pending', 'approved', 'declined')) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES Users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES Users(user_id)
);

-- Create Meeting Polls Table
CREATE TABLE MeetingPolls (
    poll_id INTEGER PRIMARY KEY AUTOINCREMENT, -- Primary Key
    creator_id INTEGER NOT NULL, -- Foreign Key referencing Users(user_id)
    event_name TEXT NOT NULL,
    url TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES Users(user_id)
);

-- Create Poll Options Table
CREATE TABLE PollOptions (
    option_id INTEGER PRIMARY KEY AUTOINCREMENT, -- Primary Key
    poll_id INTEGER NOT NULL, -- Foreign Key referencing MeetingPolls(poll_id)
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    duration INTEGER NOT NULL, -- Duration in minutes
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    FOREIGN KEY (poll_id) REFERENCES MeetingPolls(poll_id)
);

-- Create Poll Votes Table
CREATE TABLE PollVotes (
    poll_id INTEGER NOT NULL, -- Foreign Key referencing MeetingPolls(poll_id)
    voter_email TEXT NOT NULL, -- Voter's email (not referencing Users table)
    option_id INTEGER NOT NULL, -- Foreign Key referencing PollOptions(option_id)
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (poll_id, voter_email, option_id), -- Composite Primary Key
    FOREIGN KEY (poll_id) REFERENCES MeetingPolls(poll_id),
    FOREIGN KEY (option_id) REFERENCES PollOptions(option_id)
);
