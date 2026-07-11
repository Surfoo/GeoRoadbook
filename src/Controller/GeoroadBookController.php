<?php

namespace App\Controller;

use App\Roadbook\GpxParser;
use App\Roadbook\RoadbookFactory;
use App\Roadbook\RoadbookRenderer;
use App\Security\User;
use Geocaching\Enum\Environment;
use Geocaching\GeocachingSdk;
use Geocaching\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GeoroadBookController extends AbstractController
{
    /**
     * @param array<string, string> $locales
     * @param list<string>          $availableSorts
     */
    public function __construct(
        private readonly RoadbookFactory $roadbookFactory,
        private readonly GpxParser $gpxParser,
        private readonly RoadbookRenderer $renderer,
        #[Autowire('%app.locales%')]
        private readonly array $locales,
        #[Autowire('%app.available_sorts%')]
        private readonly array $availableSorts,
        #[Autowire('%env(GEOCACHING_ENV)%')]
        private readonly string $geocachingEnvironment,
        #[Autowire('%app.internal_base_url%')]
        private readonly string $internalBaseUrl,
        #[Autowire('%app.weasyprint_url%')]
        private readonly string $weasyprintUrl,
        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $publicDir,
    ) {
    }

    #[Route('/', name: 'app_homepage', methods: ['GET'])]
    public function index(): Response
    {
        $params = [
            'suffix_css_js' => 'aa',
            'locales'       => $this->locales,
        ];

        $user = $this->getUser();
        if ($user instanceof User && $user->getCredentials()) {
            try {
                $response = $this->createGeocachingSdk($user)->getUserLists('me', [
                    'types'  => 'pq',
                    'take'   => 50,
                    'fields' => 'referenceCode,name',
                ]);
                /** @var list<object{referenceCode: string, name: string}> $pocketQueryList */
                $pocketQueryList = json_decode((string) $response->getBody(), false, 512, JSON_THROW_ON_ERROR);
                usort($pocketQueryList, fn ($a, $b) => $a->name <=> $b->name);
                $params['pocketqueryList'] = $pocketQueryList;
            } catch (\Throwable) {
                // Pocket queries are optional — the upload form still works without them
            }
        }

        return $this->render('index.html.twig', $params);
    }

    #[Route('/upload', name: 'app_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $payload = $request->getPayload();

        $gpx           = (string) $payload->get('gpx', '');
        $referenceCode = (string) $payload->get('referenceCode', '');
        $locale        = $payload->get('locale');

        if ($gpx === '' && $referenceCode === '') {
            return $this->json(['success' => false, 'message' => 'A GPX file or a Pocket Query is missing.']);
        }

        if ($locale === null || $locale === '') {
            return $this->json(['success' => false, 'message' => 'Roadbook language is missing.']);
        }

        if (!array_key_exists($locale, $this->locales)) {
            return $this->json(['success' => false, 'message' => 'Roadbook language is invalid.']);
        }

        if ($referenceCode !== '') {
            try {
                $gpx = $this->downloadPocketQuery($referenceCode);
            } catch (\Throwable $e) {
                return $this->json(['success' => false, 'message' => $e->getMessage()]);
            }
        }

        try {
            $sxe = new \SimpleXMLElement($gpx);
        } catch (\Exception) {
            return $this->json(['success' => false, 'message' => 'Not a XML file.']);
        }

        $schemaLocation = (string) $sxe->attributes('xsi', true)->schemaLocation;
        preg_match('!http://www.groundspeak.com/cache/([0-9/]*)!i', $schemaLocation, $match);

        if (!array_key_exists(1, $match)) {
            return $this->json(['success' => false, 'message' => 'GPX type is incorrect.']);
        }
        if ($match[1] === '1/0') {
            return $this->json(['success' => false, 'message' => 'GPX version 1/0 is not supported, please use version 1/0/1. ' .
                '<a href="https://www.geocaching.com/account/settings/preferences">Check your preferences</a>']);
        }

        $bool = static fn ($value) => $value === true || $value === 'true';

        $sortBy = $payload->get('sort_by');
        if (!in_array($sortBy, $this->availableSorts, true)) {
            $sortBy = $this->availableSorts[0];
        }

        $displayToc       = $bool($payload->get('toc'));
        $displayHint      = $bool($payload->get('hint'));
        $displayLogs      = $bool($payload->get('logs'));
        $displaySpoilers  = $bool($payload->get('spoilers'));
        $displayWaypoints = $bool($payload->get('waypoints'));
        $hintEncrypted    = $bool($payload->get('hint_encrypted'));
        $removeImages     = $bool($payload->get('images'));

        $roadbook = $this->roadbookFactory->create();

        if (!$roadbook->create($gpx)) {
            return $this->json(['success' => false, 'message' => 'Unable to save the GPX file.']);
        }

        $options = [
            'display_note'      => $bool($payload->get('note')),
            'display_long_desc' => $bool($payload->get('long_desc')),
            'display_hint'      => $displayHint,
            'display_logs'      => $displayLogs,
            'display_waypoints' => $displayWaypoints,
            'display_spoilers'  => $displaySpoilers,
            'pagebreak'         => $bool($payload->get('pagebreak')),
        ];

        $caches = $this->gpxParser->sort($this->gpxParser->parse($gpx), $sortBy);
        $roadbook->setContent($this->renderer->render($caches, $locale, $options), $locale)->cleanHtml();

        if ($displayToc) {
            $roadbook->addToc();
        }

        if ($removeImages) {
            $roadbook->removeImages();
        }

        if ($displayHint && $hintEncrypted) {
            $roadbook->encryptHints();
        }

        $roadbook->getOnlyBody();

        $roadbook->saveFile($roadbook->getHtmlFile(), $roadbook->html);
        $roadbook->saveFile($roadbook->getJsonFile());

        return $this->json(['success' => true, 'redirect' => '/roadbook/' . $roadbook->id]);
    }

    #[Route('/roadbook/{id}', name: 'app_roadbook', requirements: ['id' => '[a-z0-9]+'], methods: ['GET'])]
    public function roadbook(string $id): Response
    {
        $roadbook = $this->getRoadbookOr404($id);

        $options = [];
        if (is_readable($roadbook->getJsonFile())) {
            $options = json_decode((string) file_get_contents($roadbook->getJsonFile()), true) ?: [];
        }

        return $this->render('edit.html.twig', [
            'suffix_css_js'     => 'aa',
            'roadbook_id'       => $roadbook->id,
            'roadbook_content'  => file_get_contents($roadbook->getHtmlFile()),
            'last_modification' => 'Last saved: ' . $roadbook->getLastSavedDate(),
            'export_options'    => $options,
            'pdf_available'     => file_exists($roadbook->getPdfFile()),
        ]);
    }

    #[Route('/roadbook/{id}/raw', name: 'app_roadbook_raw', requirements: ['id' => '[a-z0-9]+'], methods: ['GET'])]
    public function raw(string $id): Response
    {
        $roadbook = $this->getRoadbookOr404($id);

        return $this->render('raw.twig.html', [
            'suffix_css_js' => 'aa',
            'style'         => $roadbook->getCustomCss(),
            'content'       => file_get_contents($roadbook->getHtmlFile()),
        ]);
    }

    #[Route('/roadbook/{id}/save', name: 'app_roadbook_save', requirements: ['id' => '[a-z0-9]+'], methods: ['POST'])]
    public function save(string $id, Request $request): JsonResponse
    {
        $roadbook = $this->getRoadbookOr404($id);

        $content = (string) $request->getPayload()->get('content', '');
        if ($content === '') {
            return $this->json(['success' => false, 'message' => 'Empty content.']);
        }

        if (!$roadbook->saveFile($roadbook->getHtmlFile(), $content)) {
            return $this->json(['success' => false, 'message' => 'Unable to write the file.']);
        }

        return $this->json([
            'success'           => true,
            'last_modification' => 'Last saved: ' . $roadbook->getLastSavedDate(),
        ]);
    }

    #[Route('/roadbook/{id}/delete', name: 'app_roadbook_delete', requirements: ['id' => '[a-z0-9]+'], methods: ['POST'])]
    public function delete(string $id): JsonResponse
    {
        $roadbook = $this->getRoadbookOr404($id);
        $roadbook->delete();

        $this->addFlash('deleted', 'Your roadbook has been deleted.');

        return $this->json(['success' => true, 'redirect' => $this->generateUrl('app_homepage')]);
    }

    #[Route('/roadbook/{id}/export', name: 'app_roadbook_export', requirements: ['id' => '[a-z0-9]+'], methods: ['POST'])]
    public function export(string $id, Request $request): JsonResponse
    {
        $roadbook = $this->getRoadbookOr404($id);
        $payload  = $request->getPayload();

        $options = [
            'page_size'         => in_array($payload->get('page_size'), ['A4', 'A5'], true) ? $payload->get('page_size') : 'A4',
            'orientation'       => in_array($payload->get('orientation'), ['portrait', 'landscape'], true) ? $payload->get('orientation') : 'portrait',
            'margin_top'        => (int) $payload->get('margin_top', 10),
            'margin_right'      => (int) $payload->get('margin_right', 10),
            'margin_bottom'     => (int) $payload->get('margin_bottom', 10),
            'margin_left'       => (int) $payload->get('margin_left', 10),
            'header_align'      => in_array($payload->get('header_align'), ['left', 'center', 'right'], true) ? $payload->get('header_align') : 'left',
            'header_text'       => (string) $payload->get('header_text', ''),
            'header_pagination' => (bool) $payload->get('header_pagination', false),
            'footer_align'      => in_array($payload->get('footer_align'), ['left', 'center', 'right'], true) ? $payload->get('footer_align') : 'left',
            'footer_text'       => (string) $payload->get('footer_text', ''),
            'footer_pagination' => (bool) $payload->get('footer_pagination', false),
        ];

        $roadbook->saveOptions($options);

        try {
            $roadbook->exportPdf($this->internalBaseUrl, $this->weasyprintUrl);
        } catch (\RuntimeException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }

        return $this->json([
            'success' => true,
            'size'    => round(filesize($roadbook->getPdfFile()) / (1024 * 1024), 2),
        ]);
    }

    #[Route('/roadbook/{id}/pdf', name: 'app_roadbook_pdf', requirements: ['id' => '[a-z0-9]+'], methods: ['GET'])]
    public function pdf(string $id): Response
    {
        $roadbook = $this->getRoadbookOr404($id);

        if (!file_exists($roadbook->getPdfFile())) {
            throw $this->createNotFoundException('No PDF has been exported for this roadbook.');
        }

        return $this->file($roadbook->getPdfFile(), 'roadbook.pdf');
    }

    #[Route('/roadbook/{id}/zip', name: 'app_roadbook_zip', requirements: ['id' => '[a-z0-9]+'], methods: ['GET'])]
    public function zip(string $id): Response
    {
        $roadbook = $this->getRoadbookOr404($id);
        $zipFile  = $roadbook->buildZip($this->publicDir);

        return $this->file($zipFile, 'roadbook.zip')->deleteFileAfterSend();
    }

    private function createGeocachingSdk(User $user): GeocachingSdk
    {
        return new GeocachingSdk(new Options([
            'environment'  => Environment::from($this->geocachingEnvironment),
            'access_token' => (string) $user->getCredentials()?->getToken(),
        ]));
    }

    private function getRoadbookOr404(string $id): \App\Roadbook\Roadbook
    {
        $roadbook = $this->roadbookFactory->create($id);

        if (!file_exists($roadbook->getHtmlFile()) || !is_readable($roadbook->getHtmlFile())) {
            throw $this->createNotFoundException('This roadbook doesn\'t exist.');
        }

        return $roadbook;
    }

    private function downloadPocketQuery(string $referenceCode): string
    {
        $user = $this->getUser();
        if (!$user instanceof User || !$user->getCredentials()) {
            throw new \RuntimeException('You must be signed in to use a Pocket Query.');
        }

        $geocachingApi = $this->createGeocachingSdk($user);

        $tmpDirectory = sys_get_temp_dir() . '/georoadbook';
        if (!is_dir($tmpDirectory)) {
            mkdir($tmpDirectory, 0775, true);
        }
        $zipFilePath = sprintf('%s/%s.zip', $tmpDirectory, basename($referenceCode));

        $geocachingApi->getZippedPocketQuery($referenceCode, $tmpDirectory);

        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath) !== true) {
            throw new \RuntimeException('Unzipping the Pocket Query archive failed.');
        }
        $gpxFileName = $zip->statIndex(1)['name'];
        $zip->extractTo(sprintf('%s/%s', $tmpDirectory, basename($referenceCode)), [$gpxFileName]);
        $zip->close();

        return file_get_contents(sprintf('%s/%s/%s', $tmpDirectory, basename($referenceCode), $gpxFileName));
    }
}
