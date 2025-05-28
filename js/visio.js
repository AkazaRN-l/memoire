let localStream;
let peerConnection;
const configuration = {
  iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
};

document.getElementById('startButton').addEventListener('click', startCall);

function startCall() {
  // Demander l'accès à la caméra et au microphone
  navigator.mediaDevices.getUserMedia({ 
    video: true, 
    audio: true
  })
  .then(function(stream) {
    // Afficher la vidéo locale dans l'élément vidéo
    const localVideo = document.getElementById('localVideo');
    localVideo.srcObject = stream;
    localStream = stream;

    // Créer la connexion Peer après avoir capturé le flux local
    createPeerConnection();
  })
  .catch(function(err) {
    console.error('Erreur de capture vidéo/audio : ', err);
    alert('Erreur lors de l\'initialisation du flux vidéo.');
  });
}

function createPeerConnection() {
  // Créer la connexion WebRTC
  peerConnection = new RTCPeerConnection(configuration);

  // Ajouter le flux local à la connexion
  if (localStream) {
    peerConnection.addStream(localStream);
  } else {
    console.error("Flux local introuvable");
    alert("Flux local non disponible");
    return;
  }

  // Configuration des événements ICE pour gérer les candidats
  peerConnection.onicecandidate = function(event) {
    if (event.candidate) {
      sendMessage({ type: 'candidate', candidate: event.candidate });
    }
  };

  // Ajouter un flux distant lorsque reçu
  peerConnection.onaddstream = function(event) {
    const remoteVideo = document.getElementById('remoteVideo');
    remoteVideo.srcObject = event.stream;
  };

  // Créer une offre SDP et l'envoyer au serveur
  peerConnection.createOffer()
    .then(function(offer) {
      return peerConnection.setLocalDescription(offer);
    })
    .then(function() {
      sendMessage({ type: 'offer', sdp: peerConnection.localDescription });
    })
    .catch(function(error) {
      console.error('Erreur lors de la création de l\'offre : ', error);
    });
}

function sendMessage(message) {
  // Envoyer le message à ton serveur WebSocket pour transmettre les informations
  console.log('Message envoyé au serveur : ', message);
  // Assure-toi d'avoir un serveur WebSocket configuré pour transmettre ces messages
}

navigator.mediaDevices.getUserMedia({
  video: {
    width: { ideal: 640 },
    height: { ideal: 480 },
    frameRate: { ideal: 15, max: 20 }
  },
  audio: false
});

