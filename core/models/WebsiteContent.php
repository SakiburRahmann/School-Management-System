<?php
/**
 * Website Content Model
 * Handles CMS content for public website
 */

class WebsiteContent extends BaseModel {
    protected $table = 'website_content';
    protected $primaryKey = 'content_id';
    
    /**
     * Get content by page
     */
    public function getPageContent($pageName) {
        $sql = "SELECT * FROM {$this->table} WHERE page_name = :page_name 
                ORDER BY section_name, content_key";
        
        return $this->query($sql, ['page_name' => $pageName]);
    }
    
    /**
     * Get specific content
     */
    public function getContent($pageName, $sectionName, $contentKey) {
        $sql = "SELECT content_value FROM {$this->table} 
                WHERE page_name = :page_name 
                AND section_name = :section_name 
                AND content_key = :content_key";
        
        $result = $this->queryOne($sql, [
            'page_name' => $pageName,
            'section_name' => $sectionName,
            'content_key' => $contentKey
        ]);
        
        return $result['content_value'] ?? '';
    }
    
    /**
     * Update or create content
     */
    public function saveContent($pageName, $sectionName, $contentKey, $contentValue) {
        // Check if exists
        $existing = $this->queryOne(
            "SELECT content_id FROM {$this->table} 
             WHERE page_name = :page_name 
             AND section_name = :section_name 
             AND content_key = :content_key",
            [
                'page_name' => $pageName,
                'section_name' => $sectionName,
                'content_key' => $contentKey
            ]
        );
        
        if ($existing) {
            return $this->update($existing['content_id'], ['content_value' => $contentValue]);
        } else {
            return $this->create([
                'page_name' => $pageName,
                'section_name' => $sectionName,
                'content_key' => $contentKey,
                'content_value' => $contentValue
            ]);
        }
    }
    
    /**
     * Get all pages
     */
    public function getAllPages() {
        $sql = "SELECT DISTINCT page_name FROM {$this->table} ORDER BY page_name";
        return $this->query($sql);
    }
}
