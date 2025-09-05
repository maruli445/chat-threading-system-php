# Chat Threading System - PHP Web Application

This is an upgraded PHP web application that provides chat threading functionality for case management. The system allows users to create cases, add threaded messages with replies, upload images, and have real-time-like conversations.

## Features

### 🔥 New Chat Threading Features
- **Threaded Replies**: Reply to specific messages with visual threading
- **User System**: Multiple users with colored avatars
- **Real-time Updates**: Auto-refresh to check for new messages
- **Rich Text Editor**: Trix editor for formatted messages
- **Image Support**: Upload and view images in messages
- **Database Storage**: MySQL database instead of sessions

### 🎨 UI/UX Improvements
- Modern chat bubble design
- User avatars with colors
- Threaded message display with indentation
- Reply indicators and message counts
- Responsive design
- Modal image viewer

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Setup Steps

1. **Clone/Download** the files to your web server directory

2. **Database Setup**:
   - Option A: Run `setup_database.php` in your browser (recommended)
   - Option B: Import `database.sql` manually into MySQL

3. **Configure Database**:
   - Edit `config.php` and update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'chat_threading_db');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Create Upload Directory**:
   ```bash
   mkdir uploads
   chmod 755 uploads
   ```

5. **Access the Application**:
   - Visit `index.php` in your browser
   - Start creating cases and chatting!

## File Structure

```
├── index.php              # Main case list page
├── detail_case.php        # Chat threading interface
├── config.php             # Database configuration
├── setup_database.php     # Database setup script
├── ajax_check_messages.php # Auto-refresh functionality
├── database.sql           # Database schema
├── models/
│   ├── Case.php          # Case management model
│   └── Message.php       # Message/threading model
├── uploads/              # Image upload directory
├── style.css             # Main page styles
├── style_detail.css      # Detail page styles
└── README.md             # This file
```

## Usage

### Creating Cases
1. Go to `index.php`
2. Enter a case title in the form
3. Click "Simpan" to create the case

### Chat Threading
1. Click on a case title to open the chat interface
2. Type your message in the rich text editor
3. Optionally upload an image
4. Click "Kirim Pesan" to send

### Replying to Messages
1. Click the "Balas" button on any message
2. Type your reply
3. Click "Kirim Balasan"
4. Your reply will be threaded under the original message

### User Management
- The system includes 5 default users with different colored avatars
- Current user is shown at the top of the chat interface
- You can switch users by modifying the session (future enhancement)

## Database Schema

### Tables
- **cases**: Stores case information
- **users**: User accounts with avatar colors
- **messages**: Chat messages with threading support
- **message_status**: Message delivery status (future use)

### Key Features
- Foreign key relationships for data integrity
- Cascading deletes for cleanup
- Indexed columns for performance
- UTF-8 support for international characters

## Technical Details

### Threading Logic
- Messages with `parent_message_id = NULL` are root messages
- Replies have `parent_message_id` pointing to the parent message
- Visual threading uses CSS indentation based on nesting level

### Auto-refresh
- JavaScript polls `ajax_check_messages.php` every 10 seconds
- Compares message count to detect new messages
- Automatically reloads page when new messages are found

### Security Features
- SQL injection protection with prepared statements
- XSS protection with `htmlspecialchars()`
- File upload validation for images only
- Input sanitization and validation

## Customization

### Adding New Users
```sql
INSERT INTO users (username, display_name, avatar_color) 
VALUES ('newuser', 'New User', '#ff6b6b');
```

### Changing Auto-refresh Interval
Edit the interval in `detail_case.php`:
```javascript
setInterval(checkForNewMessages, 5000); // 5 seconds instead of 10
```

### Styling
- Modify `style.css` for main page styling
- Modify `style_detail.css` for chat interface styling
- Inline styles in `detail_case.php` for chat-specific elements

## Troubleshooting

### Database Connection Issues
- Check MySQL server is running
- Verify credentials in `config.php`
- Ensure database exists and user has permissions

### Upload Issues
- Check `uploads/` directory exists and is writable
- Verify PHP `upload_max_filesize` and `post_max_size` settings
- Check file permissions

### Auto-refresh Not Working
- Check browser console for JavaScript errors
- Verify `ajax_check_messages.php` is accessible
- Check PHP error logs

## Future Enhancements

- [ ] User authentication system
- [ ] Real-time WebSocket updates
- [ ] Message editing and deletion
- [ ] File attachments (not just images)
- [ ] Message search functionality
- [ ] Email notifications
- [ ] Mobile app support
- [ ] Message reactions/emojis

## License

This project is open source and available under the MIT License.
