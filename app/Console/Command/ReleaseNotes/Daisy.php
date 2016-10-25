<?php
namespace App\Console\Command\ReleaseNotes;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

use Twig_Loader_Filesystem;
use Twig_Environment;

use Swift_Message;
use Swift_SmtpTransport;
use Swift_Mailer;

class Daisy extends Command
{
    private $command;

    protected function configure()
    {
        $this->setName('daisy:release-notes')
            ->setDescription("Outputs the release notes for Daisy Central.")
            ->setDefinition(
                new InputDefinition([
                    new InputOption('notify', null, InputOption::VALUE_OPTIONAL, 'List of emails separated by comma'),
                    new InputOption('username', null, InputOption::VALUE_OPTIONAL, 'SVN Username'),
                    new InputOption('password', null, InputOption::VALUE_OPTIONAL, 'SVN Password'),
                    new InputOption('self-service', null, InputOption::VALUE_REQUIRED, 'SelfService start,end revisions'),
                    new InputOption('acc4billing', null, InputOption::VALUE_REQUIRED, 'Acc4billing start,end revisions'),
                    new InputOption('dwp', null, InputOption::VALUE_REQUIRED, 'DWP start,end revisions'),
                    new InputOption('external-users', null, InputOption::VALUE_REQUIRED, 'ExternalUsers start,end revisions'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->command = $this->getApplication()->find('svn:log');
        $svnLogOutput = new BufferedOutput();

        // self-service
        if ($input->getOption('self-service'))
        {
            $svnLogOutput->writeln('<b style="font-size: 14px">SelfService:</b>');

            $this->svnLog(
                $input,
                $svnLogOutput,
                $input->getOption('self-service'),
                getenv('DC_REPOSITORY'),
                getenv('DC_REVISION_URL')
            );

            $svnLogOutput->writeln('');
        }

        //acc4billing
        if ($input->getOption('acc4billing'))
        {
            $svnLogOutput->writeln('<b style="font-size: 14px">Acc4Billing:</b>');

            $this->svnLog(
                $input,
                $svnLogOutput,
                $input->getOption('acc4billing'),
                getenv('ACC4BILLING_REPOSITORY'),
                getenv('ACC4BILLIN_REVISION_URL')
            );

            $svnLogOutput->writeln('');
        }

        // dwp
        if ($input->getOption('dwp'))
        {
            $svnLogOutput->writeln('<b style="font-size: 14px">DWP:</b>');
            
            $this->svnLog(
                $input,
                $svnLogOutput,
                $input->getOption('dwp'),
                getenv('DWP_REPOSITORY'),
                getenv('DWP_REVISION_URL')
            );

            $svnLogOutput->writeln('');
        }

        // external-users
        if ($input->getOption('external-users'))
        {
            $svnLogOutput->writeln('<b style="font-size: 14px">External Users:</b>');

            $this->svnLog(
                $input,
                $svnLogOutput,
                $input->getOption('external-users'),
                getenv('EXTERNAL_USERS_REPOSITORY'),
                getenv('EXTERNAL_USERS_REVISION_URL')
            );

            $svnLogOutput->writeln('');
        }

        $notes = $svnLogOutput->fetch();

        $this->sendMail(explode(',', $input->getOption('notify')), $notes);

        $output->writeln($notes);
    }

    protected function svnLog($input, $output, $revisions, $repository, $revisionUrl)
    {
        $arguments = array(
            '--username'        => $input->getOption('username'),
            '--password'        => $input->getOption('password'),
            '--start'           => $this->__start($revisions),
            '--end'             => $this->__end($revisions),
            '--repository'      => $repository,
            '--revision-url'    => $revisionUrl,
        );

        $returnCode = $this->command->run(new ArrayInput($arguments), $output);
    }

    protected function sendMail($to, $message)
    {
        // Create the Transport
        $transport = Swift_SmtpTransport::newInstance()
            ->setHost(getenv('MAIL_HOST'))
            ->setPort(getenv('MAIL_PORT'))
            ->setEncryption(getenv('MAIL_ENCRYPTION'))
            ->setUsername(getenv('MAIL_USERNAME'))
            ->setPassword(getenv('MAIL_PASSWORD'));

        // Create the Mailer using your created Transport
        $mailer = Swift_Mailer::newInstance($transport);

        $loader = new Twig_Loader_Filesystem(getenv('TEMPLATES'));
        $twig = new Twig_Environment($loader);

        $mail = Swift_Message::newInstance()
            ->setSubject(sprintf('Release notes for %s', date('d/m/Y')))
            ->setFrom([getenv('MAIL_FROM_EMAIL') => getenv('MAIL_FROM_NAME')])
            ->setTo($to)
            ->setBody($twig->render('emails/release-notes.html', ['message' => $message]), 'text/html');

        // Send the message
        $result = $mailer->send($mail);
    }

    private function __start($revisions)
    {
        $parts = explode(',', $revisions);

        return $parts[0];
    }

    private function __end($revisions)
    {
        $parts = explode(',', $revisions);

        return isset($parts[1]) ? $parts[1] : 'HEAD';
    }
}
