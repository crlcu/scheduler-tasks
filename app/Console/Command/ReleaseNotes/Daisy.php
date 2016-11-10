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
use Symfony\Component\Process\Exception\ProcessFailedException;

use Carbon\Carbon;

use Twig_Loader_Filesystem;
use Twig_Environment;

use Swift_Message;
use Swift_SmtpTransport;
use Swift_Mailer;

class Daisy extends Command
{
    private $input;
    private $command;

    protected function configure()
    {
        $this->setName('daisy:release-notes')
            ->setDescription("Outputs the release notes for Daisy Central.")
            ->setDefinition(
                new InputDefinition([
                    new InputOption('notify', null, InputOption::VALUE_OPTIONAL, 'List of emails separated by comma'),
                    new InputOption('send-email', null, InputOption::VALUE_NONE, 'Send email.'),
                    new InputOption('subject', null, InputOption::VALUE_OPTIONAL, 'Email subject.'),
                    new InputOption('html', null, InputOption::VALUE_NONE, 'Output as html.'),
                    new InputOption('username', null, InputOption::VALUE_OPTIONAL, 'SVN Username'),
                    new InputOption('password', null, InputOption::VALUE_OPTIONAL, 'SVN Password'),
                    new InputOption('self-service', null, InputOption::VALUE_REQUIRED, 'SelfService start,end revisions'),
                    new InputOption('acc4billing', null, InputOption::VALUE_REQUIRED, 'Acc4billing start,end revisions'),
                    new InputOption('dwp', null, InputOption::VALUE_REQUIRED, 'DWP start,end revisions'),
                    new InputOption('external-users', null, InputOption::VALUE_REQUIRED, 'External Users start,end revisions'),
                    new InputOption('external-api', null, InputOption::VALUE_REQUIRED, 'External API start,end revisions'),
                    new InputOption('exclude-self-service', null, InputOption::VALUE_NONE, 'Exclude SelfService repository.'),
                    new InputOption('exclude-acc4billing', null, InputOption::VALUE_NONE, 'Exclude Acc4billing repository.'),
                    new InputOption('exclude-dwp', null, InputOption::VALUE_NONE, 'Exclude DWP repository.'),
                    new InputOption('exclude-external-users', null, InputOption::VALUE_NONE, 'Exclude External Users repository.'),
                    new InputOption('exclude-external-api', null, InputOption::VALUE_NONE, 'Exclude External API repository.'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->command = $this->getApplication()->find('svn:log');
        $svnLogOutput = new BufferedOutput();

        // self-service
        if (!$input->getOption('exclude-self-service'))
        {
            $svnLogOutput->writeln($this->__title('SelfService:'));

            try {
                $this->svnLog(
                    $input,
                    $svnLogOutput,
                    $input->getOption('self-service'),
                    getenv('DC_REPOSITORY'),
                    getenv('DC_REVISION_URL')
                );

                $svnLogOutput->writeln('');
            } catch (ProcessFailedException $e) {
                $svnLogOutput->writeln("Couldn't fetch updates.");
            }
        }

        //acc4billing
        if (!$input->getOption('exclude-acc4billing'))
        {
            $svnLogOutput->writeln($this->__title('Acc4Billing:'));

            try {
                $this->svnLog(
                    $input,
                    $svnLogOutput,
                    $input->getOption('acc4billing'),
                    getenv('ACC4BILLING_REPOSITORY'),
                    getenv('ACC4BILLIN_REVISION_URL')
                );

                $svnLogOutput->writeln('');
            } catch (ProcessFailedException $e) {
                $svnLogOutput->writeln("Couldn't fetch updates.");
            }
        }

        // dwp
        if (!$input->getOption('exclude-dwp'))
        {
            $svnLogOutput->writeln($this->__title('DWP:'));
            
            try {
                $this->svnLog(
                    $input,
                    $svnLogOutput,
                    $input->getOption('dwp'),
                    getenv('DWP_REPOSITORY'),
                    getenv('DWP_REVISION_URL')
                );

                $svnLogOutput->writeln('');
            } catch (ProcessFailedException $e) {
                $svnLogOutput->writeln("Couldn't fetch updates.");
            }
        }

        // external-users
        if (!$input->getOption('exclude-external-users'))
        {
            $svnLogOutput->writeln($this->__title('External Users:'));

            try {
                $this->svnLog(
                    $input,
                    $svnLogOutput,
                    $input->getOption('external-users'),
                    getenv('EXTERNAL_USERS_REPOSITORY'),
                    getenv('EXTERNAL_USERS_REVISION_URL')
                );

                $svnLogOutput->writeln('');
            } catch (ProcessFailedException $e) {
                $svnLogOutput->writeln("Couldn't fetch updates.");
            }
        }

        // external-api
        if (!$input->getOption('exclude-external-api'))
        {
            $svnLogOutput->writeln($this->__title('External API APP:'));

            try {
                $this->svnLog(
                    $input,
                    $svnLogOutput,
                    $input->getOption('external-api'),
                    getenv('EXTERNAL_API_REPOSITORY'),
                    getenv('EXTERNAL_API_REVISION_URL')
                );
            } catch (ProcessFailedException $e) {
                $svnLogOutput->writeln("Couldn't fetch updates.");
            }
        }

        $notes = $svnLogOutput->fetch();

        // Check if we have to send the email
        if ($input->getOption('send-email'))
        {
            $this->sendMail(explode(',', $input->getOption('notify')), $notes);
        }

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
            '--html'            => $input->getOption('html'),
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
            ->setSubject($this->input->getOption('subject') ? : sprintf('Release notes for %s', date('d/m/Y')))
            ->setFrom([getenv('MAIL_FROM_EMAIL') => getenv('MAIL_FROM_NAME')])
            ->setTo($to)
            ->setBody($twig->render('emails/release-notes.html', ['message' => $message]), 'text/html');

        // Send the message
        $result = $mailer->send($mail);
    }

    private function __start($revisions)
    {
        $parts = explode(',', $revisions);
        $start = $parts[0];

        try {
            if (!$start) {
                $today = Carbon::today();

                if ($today->dayOfWeek == 1)
                {
                    $start = 'last thursday';
                }
                elseif ($today->dayOfWeek == 3)
                {
                    $start = 'last tuesday';
                }
            }

            $date = new Carbon($start);
            $start = sprintf('{%s}', $date->toDateString());
        }
        catch (\Exception $e)
        {
            # do nothing
        }

        return $start;
    }

    private function __end($revisions)
    {
        $parts = explode(',', $revisions);
        $end = isset($parts[1]) ? $parts[1] : 'HEAD';

        try {
            $date = new Carbon($end);
            $end = sprintf('{%s}', $date->toDateString());
        }
        catch (\Exception $e)
        {
            # do nothing
        }

        return $end;
    }

    private function __title($title)
    {
        if ($this->input->getOption('html'))
        {
            $title = sprintf('<b style="font-size: 14px">%s</b>', $title);
        }

        return $title;
    }
}
