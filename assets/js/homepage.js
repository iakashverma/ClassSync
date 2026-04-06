// ClassSync — Homepage JavaScript
// Navbar scroll, subject filters, scroll reveal animations, mobile nav

// =============================
// Navbar Scroll Effect
// =============================
(function() {
    var nav = document.getElementById('publicNav');
    if (!nav) return;
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 60) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    });
})();

// =============================
// Mobile Navigation Toggle
// =============================
function toggleMobileNav() {
    var links = document.getElementById('navLinks');
    var actions = document.querySelector('.nav-actions');
    if (links) links.classList.toggle('open');
    if (actions) actions.classList.toggle('open');
}

// Close mobile nav when clicking a link
document.addEventListener('DOMContentLoaded', function() {
    var navLinksPub = document.querySelectorAll('.nav-link-pub');
    navLinksPub.forEach(function(link) {
        link.addEventListener('click', function() {
            var links = document.getElementById('navLinks');
            var actions = document.querySelector('.nav-actions');
            if (links) links.classList.remove('open');
            if (actions) actions.classList.remove('open');
        });
    });
});

// =============================
// Smooth Scroll for Anchor Links
// =============================
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            var targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            var target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                var offset = 80; // navbar height
                var targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({ top: targetPosition, behavior: 'smooth' });
            }
        });
    });
});

// =============================
// Subject Filter for Materials & Videos
// =============================
function filterContent(section, subjectId, clickedTab) {
    // Determine which grid and tab container to use
    var gridId = section === 'materials' ? 'materialsGrid' : 'videosGrid';
    var filterId = section === 'materials' ? 'materialFilters' : 'videoFilters';
    
    var grid = document.getElementById(gridId);
    var filterContainer = document.getElementById(filterId);
    if (!grid || !filterContainer) return;
    
    // Update active tab
    filterContainer.querySelectorAll('.filter-tab').forEach(function(tab) {
        tab.classList.remove('active');
    });
    clickedTab.classList.add('active');
    
    // Filter cards
    var cards = grid.querySelectorAll('[data-subject]');
    var emptyState = grid.querySelector('.empty-state-public');
    var visibleCount = 0;
    
    cards.forEach(function(card) {
        if (subjectId === 'all' || card.getAttribute('data-subject') === String(subjectId)) {
            card.style.display = '';
            card.style.opacity = '0';
            card.style.transform = 'translateY(15px)';
            visibleCount++;
            
            // Animate in with stagger
            setTimeout(function() {
                card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        } else {
            card.style.display = 'none';
        }
    });
    
    // Handle empty state visibility
    if (emptyState) {
        emptyState.style.display = (visibleCount === 0 && cards.length > 0) ? '' : 
                                    (cards.length === 0 ? '' : 'none');
    }
}

// =============================
// Scroll Reveal (Intersection Observer)
// =============================
document.addEventListener('DOMContentLoaded', function() {
    var revealElements = document.querySelectorAll('.scroll-reveal');
    
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -40px 0px'
        });
        
        revealElements.forEach(function(el) {
            observer.observe(el);
        });
    } else {
        // Fallback: show all immediately
        revealElements.forEach(function(el) {
            el.classList.add('revealed');
        });
    }
});
