// Live search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const posts = document.querySelectorAll('.post-card');
    
    posts.forEach(post => {
        const title = post.querySelector('h2').textContent.toLowerCase();
        const content = post.querySelector('.post-content').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || content.includes(searchTerm)) {
            post.style.display = 'block';
        } else {
            post.style.display = 'none';
        }
    });
});

// Lazy loading for images and videos
const lazyLoad = () => {
    const lazyElements = document.querySelectorAll('[data-src]');
    
    lazyElements.forEach(el => {
        if (el.getBoundingClientRect().top < window.innerHeight + 100) {
            el.src = el.getAttribute('data-src');
            el.removeAttribute('data-src');
        }
    });
};

window.addEventListener('scroll', lazyLoad);
window.addEventListener('resize', lazyLoad);
lazyLoad(); // Initial load

// AJAX-based post loading
document.querySelectorAll('.load-more').forEach(button => {
    button.addEventListener('click', function() {
        const page = this.dataset.page;
        const category = this.dataset.category;
        
        fetch(`/api/posts.php?page=${page}&category=${category}`)
            .then(response => response.json())
            .then(data => {
                // Render new posts
                data.posts.forEach(post => {
                    // Create post element and append
                });
                
                // Update load more button
                if (data.page < data.total_pages) {
                    this.dataset.page = data.page + 1;
                } else {
                    this.style.display = 'none';
                }
            });
    });
});