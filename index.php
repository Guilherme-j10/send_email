<?php

    require_once 'vendor/autoload.php';

    function send_email()
    {   
        $url_service = 'http://newsapi.org/v2/top-headlines?country=br&category=technology&apiKey=89a8dd20f91440459c51f01600556363';
        $url_client = 'https://apserverjson.herokuapp.com/pessoas';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_client);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        $json_cliente = json_decode($result, true);

        $email_cliente = [];

        for($i = 0; $i < sizeof($json_cliente); $i++){
            $email_cliente[] .= $json_cliente[$i]['email'];

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("elevenstack@gmail.com", "Daily Notice");
            $email->setSubject('Daily Notice about '.$json_cliente[$i]['categoria_notice']);
            $email->addTo($json_cliente[$i]['email'], 'User exemple');

            $ch1 = curl_init();
            curl_setopt($ch1, CURLOPT_URL, $url_service);
            curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, 'GET');
            $response = curl_exec($ch1);
            curl_close($ch1);

            $json_return = json_decode($response, true);

            $content = '<main style="width: 100%; background-color: #fff; min-height: 100vh; display: flex; justify-content: center; align-items: center; flex-direction: column;">';
                for($k=0; $k < 20; $k++){ 
                    $content .= '
                        <section style="width: 100%; margin-bottom: 15px; padding: 10px; border-radius: 5px; background-color: #fff; box-shadow: 10px 10px 15px 1px #ccc;">
                            <img style="max-width: 100%;" src="'.$json_return['articles'][$k]['urlToImage'].'">
                            <small style="margin-top: 10px; font-family: Arial; text-align: left;"><strong>WEBSITE: </strong>'.$json_return['articles'][$k]['source']['name'].'</small>
                            <h3 style="font-family: Arial; margin-bottom: 0px; text-align: justfy;">'.$json_return['articles'][$k]['title'].'</h3>
                            <p style="text-align: left; font-family: Arial; color: #777; font-style: italic; font-size: 13px;">'.$json_return['articles'][$k]['description'].'</p>
                            <p style="font-family: Arial; margin-bottom: 10px; text-align: justify; color: #666;">'.$json_return['articles'][$k]['content'].'</p>
                            <a style="text-decoration: none; color: #fff; display: block; width: 100%; background-color: #333; border-radius: 5px; text-align: center; padding: 12px 0px; font-family: Arial;" href="'.$json_return['articles'][$k]['url'].'">Visitar site</a>
                        </section>
                    ';
                }
            $content .= '</main>';

            $email->addContent('text/html', $content);
            $sendgrid = new \SendGrid('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');
            $response = $sendgrid->send($email);
        }
    }

    while (true) {
        date_default_timezone_set('America/Sao_Paulo');
        $time = date('H:i');
        if($time == "07:00" OR $time == "14:30" OR $time == "21:00"){
            send_email();
            echo 'ENVIADO - '.date('H:i:s').PHP_EOL;
            sleep(60);
        }
    }