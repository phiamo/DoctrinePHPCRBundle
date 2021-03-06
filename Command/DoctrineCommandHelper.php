<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Bundle\PHPCRBundle\Command;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Doctrine\ODM\PHPCR\Tools\Console\Helper\DocumentManagerHelper;
use PHPCR\Util\Console\Helper\PhpcrHelper;

use Jackalope\Tools\Console\Helper\DoctrineDbalHelper;
use Jackalope\Transport\DoctrineDBAL\Client as DbalClient;
use Jackalope\Transport\Jackrabbit\Client as JackrabbitClient;
use Jackalope\Session as JackalopeSession;

/**
 * Provides helper methods to configure doctrine PHPCR-ODM commands
 * in the context of bundles and multiple sessions/document managers.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
abstract class DoctrineCommandHelper
{
    static public function setApplicationPHPCRSession(Application $application, $connName)
    {
        /** @var $registry ManagerRegistry */
        $registry = $application->getKernel()->getContainer()->get('doctrine_phpcr');
        $session = $registry->getConnection($connName);

        $helperSet = $application->getHelperSet();
        if (class_exists('Doctrine\ODM\PHPCR\Version')) {
            $helperSet->set(new DocumentManagerHelper($session));
        } else {
            $helperSet->set(new PhpcrHelper($session));
        }

        if ($session instanceof JackalopeSession && $session->getTransport() instanceof DbalClient) {
            $helperSet->set(new DoctrineDBALHelper($session->getTransport()->getConnection()));
        }
    }

    static public function setApplicationDocumentManager(Application $application, $dmName)
    {
        /** @var $registry ManagerRegistry */
        $registry = $application->getKernel()->getContainer()->get('doctrine_phpcr');
        $documentManager = $registry->getManager($dmName);

        $helperSet = $application->getHelperSet();
        $helperSet->set(new DocumentManagerHelper(null, $documentManager));
    }
}
