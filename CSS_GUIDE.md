# CSS Classes Quick Reference Guide

## How to Use the New CSS Design System

All PHP files already link to `style/style.css` which automatically imports all the modular CSS files. Simply use the classes below in your HTML.

### Layout Classes

#### Container & Spacing
```html
<div class="container">        <!-- Max-width container with padding -->
<div class="section">          <!-- White card section with padding -->
<div class="section-header">   <!-- Section header with border -->
```

#### Grid System
```html
<div class="grid grid-cols-2">  <!-- 2-column grid -->
<div class="grid grid-cols-3">  <!-- 3-column grid -->
<div class="grid grid-cols-4">  <!-- 4-column grid -->
<div class="dashboard-grid">    <!-- Auto-fit grid for dashboard stats -->
```

### Components

#### Buttons
```html
<button class="btn btn-primary">Primary Button</button>
<button class="btn btn-secondary">Secondary Button</button>
<button class="btn btn-success">Success Button</button>
<button class="btn btn-danger">Delete Button</button>
<button class="btn btn-sm">Small Button</button>
<button class="btn btn-lg">Large Button</button>
```

#### Cards
```html
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Card Title</h3>
    </div>
    <div class="card-body">
        Card content here
    </div>
</div>

<!-- Stat Card (for dashboard) -->
<div class="stat-card">
    <h3>Label</h3>
    <div class="stat-value">123</div>
</div>
```

#### Alerts
```html
<div class="alert alert-success">Success message</div>
<div class="alert alert-error">Error message</div>
<div class="alert alert-warning">Warning message</div>
<div class="alert alert-info">Info message</div>

<!-- Legacy classes still work -->
<p class="success">Success message</p>
<p class="error">Error message</p>
```

#### Badges
```html
<span class="badge badge-primary">Primary</span>
<span class="badge badge-success">Active</span>
<span class="badge badge-error">Inactive</span>
<span class="badge badge-warning">Pending</span>
```

### Forms

#### Form Structure
```html
<form>
    <div class="form-group">
        <label class="form-label">Name</label>
        <input type="text" class="form-input" required>
    </div>
    
    <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" class="form-input">
        <span class="form-help">We'll never share your email</span>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="button" class="btn btn-secondary">Cancel</button>
    </div>
</form>
```

#### Form Controls
```html
<input type="text" class="form-input">
<input type="email" class="form-input">
<input type="password" class="form-input">
<input type="number" class="form-input">
<input type="date" class="form-input">
<select class="form-select">...</select>
<textarea class="form-textarea"></textarea>
```

### Tables

#### Basic Table
```html
<div class="table-container">
    <div class="table-header">
        <h3>Table Title</h3>
        <a href="#" class="btn btn-primary btn-sm">Add New</a>
    </div>
    
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>John Doe</td>
                    <td class="table-actions">
                        <a href="#" class="action-view">View</a>
                        <a href="#" class="action-edit">Edit</a>
                        <a href="#" class="action-delete">Delete</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

### Utility Classes

#### Spacing
```html
<div class="mt-4">   <!-- margin-top -->
<div class="mb-4">   <!-- margin-bottom -->
<div class="mt-8">   <!-- larger margin-top -->
```

#### Text
```html
<p class="text-center">  <!-- center align -->
<p class="text-right">   <!-- right align -->
<p class="text-muted">   <!-- gray text -->
```

#### Flexbox
```html
<div class="flex">              <!-- display: flex -->
<div class="flex items-center"> <!-- align items center -->
<div class="flex justify-between"> <!-- space between -->
<div class="flex gap-4">        <!-- gap between items -->
```

## CSS Variables

You can use these CSS variables anywhere:

### Colors
```css
var(--primary-600)    /* Main blue */
var(--success-600)    /* Green */
var(--error-600)      /* Red */
var(--warning-600)    /* Orange */
var(--gray-600)       /* Gray */
```

### Spacing
```css
var(--space-2)   /* 0.5rem */
var(--space-4)   /* 1rem */
var(--space-6)   /* 1.5rem */
var(--space-8)   /* 2rem */
```

### Border Radius
```css
var(--radius-sm)   /* 0.375rem */
var(--radius-md)   /* 0.5rem */
var(--radius-lg)   /* 0.75rem */
var(--radius-xl)   /* 1rem */
```

### Shadows
```css
var(--shadow-sm)
var(--shadow-md)
var(--shadow-lg)
var(--shadow-xl)
```

## Examples

### Dashboard Stats
```html
<div class="dashboard-grid">
    <div class="stat-card">
        <h3>Total Students</h3>
        <div class="stat-value">150</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
        <h3>Total Teachers</h3>
        <div class="stat-value">25</div>
    </div>
</div>
```

### Management Page
```html
<main>
    <div class="page-header">
        <h2 class="page-title">Manage Students</h2>
        <p class="page-subtitle">Add, edit, and manage student records</p>
    </div>
    
    <div class="section">
        <h3>Add New Student</h3>
        <form>
            <!-- form fields here -->
        </form>
    </div>
    
    <div class="table-container">
        <!-- table here -->
    </div>
</main>
```

## Notes

- All existing pages already link to `style/style.css`
- The CSS is modular and organized in separate files
- Legacy classes (`.success`, `.error`) still work for backward compatibility
- The design is fully responsive and mobile-friendly
- All components use CSS variables for easy theming
