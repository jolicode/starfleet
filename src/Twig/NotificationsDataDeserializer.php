<?php 

namespace App\Twig;

use App\Entity\Submit;
use App\Entity\User;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NotificationsDataDeserializer extends AbstractExtension
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('deserialize', [$this, 'deserialize']),
        ];
    }

    public function deserialize(string $serializedData, string $class, string $format = 'json')
    {
        return $this->serializer->deserialize($serializedData, $class, $format);
    }
}
