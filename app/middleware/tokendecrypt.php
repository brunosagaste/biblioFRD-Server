<?php 

use App\Lib\EncryptionManager;
use Slim\Middleware\JwtAuthentication\RequestPathRule;

class TokenDecrypt {
    public function __construct(array $options = []) {
        $this->rules = new \SplStack;
        $this->options = $options;

        $this->addRule(new RequestPathRule([
            "path" => $this->options["path"]
        ]));
    }

    public function __invoke($request, $response, $next) {

        if (false === $this->shouldAuthenticate($request)) {
            return $next($request, $response);
        }

        $encryptedToken = $request->getHeaderLine('Auth');
        $id = $request->getHeaderLine('Id'); 

        $encryptionmgr = new EncryptionManager();
        $decryptedToken = $encryptionmgr->decrypt($encryptedToken, $id);
        if (!$decryptedToken) {
            return $response->withJson(['error' => true, 'status' => 401, 'message' => 'Error en el token', 'developerMessage' => 'wrongtoken'], 401);
        }
        $request = $request->withAddedHeader('Authorization', 'Bearer ' . $decryptedToken);

        return $next($request, $response);
    }

    public function shouldAuthenticate($request) {
        foreach ($this->rules as $callable) {
            if (false === $callable($request)) {
                return false;
            }
        }
        return true;
    }

    public function addRule($callable) {
        $this->rules->push($callable);
        return $this;
    }
}