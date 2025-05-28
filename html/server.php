// server.php
<?php
require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class VideoConferenceServer implements MessageComponentInterface {
    protected $clients;
    protected $rooms; // Pour gérer les salles de visio par niveau

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->rooms = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "Nouvelle connexion! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (!$data) return;
        
        // Gestion des différents types de messages
        switch ($data['type']) {
            case 'join':
                // Un utilisateur rejoint une salle (niveau)
                $niveau = $data['niveau'];
                if (!isset($this->rooms[$niveau])) {
                    $this->rooms[$niveau] = [];
                }
                $this->rooms[$niveau][$from->resourceId] = $from;
                $from->niveau = $niveau;
                $from->role = $data['role']; // 'enseignant' ou 'etudiant'
                break;
                
            case 'signal':
                // Transmission du signal WebRTC
                $targetId = $data['target'];
                foreach ($this->clients as $client) {
                    if ($client->resourceId == $targetId) {
                        $client->send($msg);
                        break;
                    }
                }
                break;
                
            case 'broadcast':
                // Diffusion à tous les participants de la même salle
                if (isset($from->niveau)) {
                    foreach ($this->rooms[$from->niveau] as $client) {
                        if ($client !== $from) {
                            $client->send($msg);
                        }
                    }
                }
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        if (isset($conn->niveau)) {
            unset($this->rooms[$conn->niveau][$conn->resourceId]);
        }
        $this->clients->detach($conn);
        echo "Connexion fermée! ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Erreur : {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new VideoConferenceServer()
        )
    ),
    8080
);

echo "Serveur WebSocket de visioconférence lancé sur le port 8080...\n";
$server->run();