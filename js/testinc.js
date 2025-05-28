document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la visioconférence
    const startBtn = document.getElementById('start-meeting');
    const classSelect = document.getElementById('class-level');
    
    startBtn.addEventListener('click', function() {
        const selectedClass = classSelect.value;
        
        if (!selectedClass) {
            alert('Veuillez sélectionner une classe');
            return;
        }
        
        // Intégration Zoom/WebRTC
        const meetingId = generateMeetingId(selectedClass);
        launchVideoConference(meetingId, selectedClass);
    });
    
    function generateMeetingId(classLevel) {
        const prefix = classLevel.replace(/\s+/g, '').toLowerCase();
        return `${prefix}-${Math.random().toString(36).substr(2, 8)}`;
    }
    
    function launchVideoConference(meetingId, classLevel) {
        // Remplacer par ton API de visio (Zoom, Jitsi, etc.)
        console.log(`Lancement de la visioconférence pour ${classLevel} (ID: ${meetingId})`);
        
        // Exemple avec lien Zoom (à adapter)
        const zoomLink = `https://zoom.us/j/123456789?pwd=${meetingId}`;
        window.open(zoomLink, '_blank');
        
        // Alternative pour WebRTC maison:
        // window.location.href = `video-conf.php?class=${encodeURIComponent(classLevel)}&id=${meetingId}`;
    }
    
    // Animation des cartes
    const cards = document.querySelectorAll('.function-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
            card.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
            card.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.05)';
        });
    });
});