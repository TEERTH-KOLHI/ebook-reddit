import re
import os

FILE_PATH = "c:/Users/teerthkolhi/Desktop/ebook/reddit-to-riches/index.html"

# AGREED OPTIMIZED ORDER
SECTION_ORDER = [
    "hero-section",
    "authority-section",
    "why-reddit-section",
    "reddit-stats-section",
    "bank-proof-section",
    "big-promise-section",
    "for-you-section",
    "not-for-you-section",
    "results-section",
    "sneak-peek-section",
    "learn-section",
    "about-author-section",
    "testimonials-section",
    "deliverables-section",
    "pricing-section",
    "faq-section"
]

def reorder_sections():
    if not os.path.exists(FILE_PATH):
        print(f"Error: File not found at {FILE_PATH}")
        return

    with open(FILE_PATH, "r", encoding="utf-8") as f:
        content = f.read()

    # 1. Find the start of the first section (Hero)
    # Use regex to find the first <section declaration
    first_section_match = re.search(r'<section class="', content)
    if not first_section_match:
        print("Error: No sections found.")
        return
    
    # We want to capture everything before the first section tag
    # The comment <!-- Hero Section --> usually precedes it.
    start_marker = "<!-- Hero Section -->"
    start_pos = content.find(start_marker)
    if start_pos == -1:
         start_pos = first_section_match.start()

    # 2. Find the end of the last section
    # The footer starts with <footer class="trust-footer">
    footer_start_match = re.search(r'<footer class="trust-footer">', content)
    
    if not footer_start_match:
         print("Error: Footer not found")
         return
         
    footer_pos = footer_start_match.start()
    
    # Extract the header block (everything before the first section)
    header_block = content[:start_pos]
    
    # Extract the body block containing all sections
    # map class name to content
    section_map = {}
    
    # Get all class names currently in the file to iterate
    present_sections = re.findall(r'<section class="([^"]+)"', content)
    print(f"Found sections in current file: {present_sections}")
    
    for sec_name in present_sections:
        # Pattern: Optional comment, then section start, content, section end.
        pattern = r'(<!--[ \w]+Section -->\s*<section class="' + re.escape(sec_name) + r'".*?</section>)'
        match = re.search(pattern, content, re.DOTALL)
        
        if match:
            section_map[sec_name] = match.group(1)
        else:
            # Try without the comment prefix
            pattern_no_comment = r'(<section class="' + re.escape(sec_name) + r'".*?</section>)'
            match_no_comment = re.search(pattern_no_comment, content, re.DOTALL)
            if match_no_comment:
                 section_map[sec_name] = match_no_comment.group(1)
            else:
                 print(f"Warning: Could not extract content for {sec_name}")

    # Reassemble with OPTIMIZED ORDER
    new_body_content = ""
    
    for sec_name in SECTION_ORDER:
        if sec_name in section_map:
            new_body_content += section_map[sec_name] + "\n\n    "
        else:
            print(f"Warning: Desired section {sec_name} not found in extracted map.")
            
    # Tail block
    bottom_block = content[footer_pos:]
    
    # Combine
    final_output = header_block + new_body_content + bottom_block
    
    # Write back
    with open(FILE_PATH, "w", encoding="utf-8") as f:
        f.write(final_output)
        
    print("Optimization reorder complete.")

if __name__ == "__main__":
    reorder_sections()
