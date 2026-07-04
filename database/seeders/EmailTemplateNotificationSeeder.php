<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateNotificationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $key => $data) {
            EmailTemplate::updateOrCreate(
                ['key' => $key],
                [
                    'name'       => EmailTemplate::TEMPLATES[$key]['name'],
                    'subject'    => EmailTemplate::TEMPLATES[$key]['default_subject'],
                    'body'       => $data['body'],
                    'variables'  => EmailTemplate::TEMPLATES[$key]['variables'],
                    'is_active'  => true,
                ]
            );
        }
    }

    private function templates(): array
    {
        $btn = fn(string $label, string $url) => '
<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin:0 0 28px 0;">
  <tr>
    <td align="center">
      <!--[if mso]>
      <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word"
        href="' . $url . '" style="height:48px;v-text-anchor:middle;width:240px;"
        arcsize="17%" stroke="f" fillcolor="#14A98C">
        <w:anchorlock/>
        <center style="color:#ffffff;font-family:\'Segoe UI\',sans-serif;font-size:14px;font-weight:600;">' . $label . '</center>
      </v:roundrect>
      <![endif]-->
      <!--[if !mso]><!-->
      <a href="' . $url . '" target="_blank"
         style="display:inline-block;background-color:#14A98C;color:#ffffff !important;text-decoration:none;font-family:\'Geist\',-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif;font-size:14px;font-weight:600;letter-spacing:-0.01em;padding:14px 32px;border-radius:8px;">' . $label . '</a>
      <!--<![endif]-->
    </td>
  </tr>
</table>';

        $note = fn(string $text) => '
<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
  <tr>
    <td style="background-color:#E6F4F0;border-radius:8px;padding:14px 20px;">
      <p style="font-family:\'Geist\',-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif;font-size:13px;line-height:1.6;color:#0B6F5C;margin:0;">' . $text . '</p>
    </td>
  </tr>
</table>';

        $overline = fn(string $label) =>
            '<p style="font-family:\'JetBrains Mono\',\'Courier New\',monospace;font-size:11px;font-weight:500;color:#14A98C;letter-spacing:0.1em;text-transform:uppercase;margin:0 0 16px 0;">' . $label . '</p>';

        $h1 = fn(string $text) =>
            '<h1 style="font-family:\'Newsreader\',Georgia,\'Times New Roman\',serif;font-size:30px;font-weight:400;font-style:italic;color:#0F1623;line-height:1.25;margin:0 0 24px 0;">' . $text . '</h1>';

        $p = fn(string $html) =>
            '<p style="font-family:\'Geist\',-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif;font-size:15px;line-height:1.7;color:#2E3850;margin:0 0 28px 0;">' . $html . '</p>';

        return [

            // ── Consul nominated ─────────────────────────────────────────────
            'consul_nominated' => ['body' =>
                $overline('Statut du compte') .
                $h1('Vous êtes maintenant<br>Consul LeadXchange') .
                $p('Bonjour <strong>{{name}}</strong>,<br><br>
Félicitations ! Vous venez d\'être nommé <strong>Consul</strong> sur LeadXchange par l\'administration.
En tant que Consul, vous bénéficiez d\'une visibilité renforcée, d\'un accès étendu à la plateforme
et d\'un rôle actif dans le développement de votre réseau régional.') .
                $btn('Accéder à mon dashboard', '{{dashboard_url}}') .
                $note('Ce statut vous a été attribué manuellement par l\'équipe LeadXchange. Pour toute question, contactez notre support.')
            ],

            // ── Ambassador nominated (direct) ────────────────────────────────
            'ambassador_nominated' => ['body' =>
                $overline('Statut du compte') .
                $h1('Vous êtes maintenant<br>Ambassadeur LeadXchange') .
                $p('Bonjour <strong>{{name}}</strong>,<br><br>
Félicitations ! Vous venez d\'être nommé <strong>Ambassadeur</strong> sur LeadXchange par l\'administration.
Ce statut distingué témoigne de votre engagement et de votre contribution à la communauté.
Votre profil est désormais mis en avant auprès de l\'ensemble du réseau.') .
                $btn('Accéder à mon dashboard', '{{dashboard_url}}') .
                $note('Ce statut vous a été attribué manuellement par l\'équipe LeadXchange. Pour toute question, contactez notre support.')
            ],

            // ── Ambassador request approved ──────────────────────────────────
            'ambassador_approved' => ['body' =>
                $overline('Demande approuvée') .
                $h1('Votre demande Ambassadeur<br>a été acceptée') .
                $p('Bonjour <strong>{{name}}</strong>,<br><br>
Bonne nouvelle ! Votre demande de rôle <strong>Ambassadeur</strong> a été examinée et approuvée par notre équipe.
Vous avez désormais accès à toutes les fonctionnalités Ambassadeur et votre profil bénéficie
d\'une mise en avant privilégiée sur la plateforme.') .
                $btn('Accéder à mon dashboard', '{{dashboard_url}}') .
                $note('Votre demande a été traitée par l\'équipe d\'administration LeadXchange. Pour toute question, contactez notre support.')
            ],

            // ── Ambassador request rejected ──────────────────────────────────
            'ambassador_rejected' => ['body' =>
                $overline('Demande non approuvée') .
                $h1('Votre demande Ambassadeur<br>n\'a pas pu aboutir') .
                $p('Bonjour <strong>{{name}}</strong>,<br><br>
Après examen, votre demande de rôle Ambassadeur n\'a pas pu être approuvée pour le moment.') .
                '<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin:0 0 28px 0;">
  <tr>
    <td style="background-color:#FEF3C7;border-left:3px solid #F59E0B;border-radius:0 6px 6px 0;padding:14px 18px;">
      <p style="font-family:\'JetBrains Mono\',\'Courier New\',monospace;font-size:10px;font-weight:500;color:#92400E;letter-spacing:0.08em;text-transform:uppercase;margin:0 0 6px 0;">Motif</p>
      <p style="font-family:\'Geist\',-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif;font-size:14px;line-height:1.6;color:#78350F;margin:0;">{{reason}}</p>
    </td>
  </tr>
</table>' .
                $p('N\'hésitez pas à compléter votre profil et à soumettre une nouvelle demande. Votre statut Consul reste inchangé.') .
                $btn('Compléter mon profil', '{{profile_url}}') .
                $note('Cette décision a été prise par l\'équipe d\'administration LeadXchange. Pour contester, contactez notre support.')
            ],

            // ── Plan purchased ───────────────────────────────────────────────
            'plan_purchased' => ['body' =>
                $overline('Confirmation d\'abonnement') .
                $h1('Votre plan {{plan_label}}<br>est maintenant actif') .
                $p('Bonjour <strong>{{name}}</strong>,<br><br>
Merci pour votre abonnement ! Votre plan <strong>{{plan_label}}</strong> est désormais actif.
Vous avez accès à toutes les fonctionnalités incluses dans votre formule.
Profitez du réseau LeadXchange pour développer vos opportunités professionnelles.') .
                '<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin:0 0 28px 0;">
  <tr>
    <td>
      <table cellpadding="0" cellspacing="0" border="0" role="presentation"
             style="width:100%;background-color:#F4F5F8;border-radius:10px;overflow:hidden;">
        <tr>
          <td style="padding:20px 24px;">
            <p style="font-family:\'JetBrains Mono\',\'Courier New\',monospace;font-size:10px;font-weight:500;color:#6C7691;letter-spacing:0.08em;text-transform:uppercase;margin:0 0 8px 0;">Plan souscrit</p>
            <p style="font-family:\'Newsreader\',Georgia,serif;font-size:22px;font-weight:600;color:#0F1623;margin:0;">{{plan_label}}</p>
          </td>
          <td style="padding:20px 24px;text-align:right;vertical-align:middle;">
            <span style="display:inline-block;background-color:#E6F4F0;color:#0B6F5C;font-family:\'Geist\',-apple-system,sans-serif;font-size:12px;font-weight:600;padding:4px 12px;border-radius:99px;">Actif</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>' .
                $btn('Accéder à mon dashboard', '{{dashboard_url}}') .
                $note('Cet email confirme votre abonnement LeadXchange. Conservez-le pour vos archives. Pour toute question, contactez notre support.')
            ],

            // ── Plan changed (admin) ─────────────────────────────────────────
            'plan_changed' => ['body' =>
                $overline('Mise à jour du plan') .
                $h1('Votre plan a été<br>mis à jour') .
                $p('Bonjour <strong>{{name}}</strong>,<br><br>
Votre plan LeadXchange a été modifié par l\'administration.
Votre nouveau plan est désormais <strong>{{plan_label}}</strong>.
Reconnectez-vous à la plateforme pour bénéficier de vos nouveaux accès.') .
                '<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin:0 0 28px 0;">
  <tr>
    <td>
      <table cellpadding="0" cellspacing="0" border="0" role="presentation"
             style="width:100%;background-color:#F4F5F8;border-radius:10px;overflow:hidden;">
        <tr>
          <td style="padding:20px 24px;">
            <p style="font-family:\'JetBrains Mono\',\'Courier New\',monospace;font-size:10px;font-weight:500;color:#6C7691;letter-spacing:0.08em;text-transform:uppercase;margin:0 0 8px 0;">Nouveau plan</p>
            <p style="font-family:\'Newsreader\',Georgia,serif;font-size:22px;font-weight:600;color:#0F1623;margin:0;">{{plan_label}}</p>
          </td>
          <td style="padding:20px 24px;text-align:right;vertical-align:middle;">
            <span style="display:inline-block;background-color:#E6F4F0;color:#0B6F5C;font-family:\'Geist\',-apple-system,sans-serif;font-size:12px;font-weight:600;padding:4px 12px;border-radius:99px;">Actif</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>' .
                $btn('Accéder à mon dashboard', '{{dashboard_url}}') .
                $note('Ce changement a été effectué par l\'équipe d\'administration LeadXchange. Pour toute question, contactez notre support.')
            ],

        ];
    }
}
