import fs from "node:fs";
import path from "node:path";
import process from "node:process";

function fail(message, error = null) {
  console.error(message);

  if (error) {
    console.error(error.stack || error.message || error);
  }

  process.exit(1);
}

async function loadPuppeteer() {
  try {
    const module = await import("puppeteer-core");

    return {
      puppeteer: module.default,
      isCore: true,
    };
  } catch (coreError) {
    try {
      const module = await import("puppeteer");

      return {
        puppeteer: module.default,
        isCore: false,
      };
    } catch (fullError) {
      fail(
        "Neither puppeteer-core nor puppeteer is installed. Run: npm install puppeteer-core OR php artisan premium-pdf:install --npm",
        fullError,
      );
    }
  }
}

function normalizePath(customPath = null) {
  if (!customPath) {
    return null;
  }

  return String(customPath)
    .replace(/^["']|["']$/g, "")
    .trim();
}

function browserCandidates() {
  const platform = process.platform;

  if (platform === "win32") {
    const localAppData = process.env.LOCALAPPDATA || "";

    return [
      "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe",
      "C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe",
      localAppData
        ? path.join(localAppData, "Google\\Chrome\\Application\\chrome.exe")
        : null,

      "C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe",
      "C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe",
      localAppData
        ? path.join(localAppData, "Microsoft\\Edge\\Application\\msedge.exe")
        : null,

      "C:\\Program Files\\BraveSoftware\\Brave-Browser\\Application\\brave.exe",
      "C:\\Program Files (x86)\\BraveSoftware\\Brave-Browser\\Application\\brave.exe",
      localAppData
        ? path.join(
            localAppData,
            "BraveSoftware\\Brave-Browser\\Application\\brave.exe",
          )
        : null,

      "C:\\Program Files\\Chromium\\Application\\chrome.exe",
      "C:\\Program Files (x86)\\Chromium\\Application\\chrome.exe",
    ].filter(Boolean);
  }

  if (platform === "darwin") {
    return [
      "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome",
      "/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge",
      "/Applications/Chromium.app/Contents/MacOS/Chromium",
      "/Applications/Brave Browser.app/Contents/MacOS/Brave Browser",
    ];
  }

  return [
    "/usr/bin/google-chrome",
    "/usr/bin/google-chrome-stable",
    "/usr/bin/chromium",
    "/usr/bin/chromium-browser",
    "/snap/bin/chromium",
    "/usr/bin/microsoft-edge",
    "/usr/bin/microsoft-edge-stable",
    "/usr/bin/brave-browser",
    "/usr/bin/brave-browser-stable",
  ];
}

function findBrowserExecutable(customPath = null) {
  const normalizedCustomPath = normalizePath(customPath);

  if (normalizedCustomPath && fs.existsSync(normalizedCustomPath)) {
    return normalizedCustomPath;
  }

  for (const candidate of browserCandidates()) {
    if (candidate && fs.existsSync(candidate)) {
      return candidate;
    }
  }

  return null;
}

const jsonPath = process.argv[2];

if (!jsonPath) {
  fail("Missing JSON payload path.");
}

if (!fs.existsSync(jsonPath)) {
  fail(`JSON payload not found: ${jsonPath}`);
}

let payload;

try {
  payload = JSON.parse(fs.readFileSync(jsonPath, "utf8"));
} catch (error) {
  fail("Invalid JSON payload.", error);
}

if (!payload.url && !payload.html) {
  fail("Either url or html is required.");
}

if (!payload.output) {
  fail("Output PDF path is required.");
}

const outputDir = path.dirname(payload.output);

if (!fs.existsSync(outputDir)) {
  fs.mkdirSync(outputDir, { recursive: true });
}

const { puppeteer, isCore } = await loadPuppeteer();

const executablePath = findBrowserExecutable(payload.browser?.executablePath);

const browserOptions = {
  headless: true,
  args: payload.browser?.args || [],
};

if (executablePath) {
  browserOptions.executablePath = executablePath;
} else if (isCore) {
  fail(
    [
      "No Chromium-based browser found.",
      "",
      "Install Google Chrome, Microsoft Edge, Chromium, or Brave Browser.",
      "",
      "Or set browser path in .env:",
      'PREMIUM_PDF_BROWSER_PATH="C:/Program Files/Google/Chrome/Application/chrome.exe"',
      'PREMIUM_PDF_BROWSER_PATH="C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe"',
      "PREMIUM_PDF_BROWSER_PATH=/usr/bin/google-chrome",
      "",
      "If no browser is installed, run:",
      "php artisan premium-pdf:install --browser",
    ].join("\n"),
  );
}

let browser;

try {
  browser = await puppeteer.launch(browserOptions);

  const page = await browser.newPage();

  const timeout = payload.page?.timeout || 120000;
  const waitUntil = payload.page?.waitUntil || "networkidle0";
  const mediaType = payload.page?.mediaType || "screen";

  page.setDefaultTimeout(timeout);
  page.setDefaultNavigationTimeout(timeout);

  await page.emulateMediaType(mediaType);

  if (payload.url) {
    await page.goto(payload.url, {
      waitUntil,
      timeout,
    });
  } else {
    await page.setContent(payload.html, {
      waitUntil,
      timeout,
    });
  }

  await page.pdf({
    path: payload.output,
    format: payload.pdf?.format || "A4",
    landscape: Boolean(payload.pdf?.landscape),
    printBackground: payload.pdf?.printBackground !== false,
    preferCSSPageSize: payload.pdf?.preferCSSPageSize !== false,
    margin: payload.pdf?.margin || {
      top: "10mm",
      right: "10mm",
      bottom: "10mm",
      left: "10mm",
    },
  });

  await browser.close();

  console.log(`PDF generated: ${payload.output}`);
} catch (error) {
  if (browser) {
    await browser.close().catch(() => {});
  }

  fail("PDF rendering failed.", error);
}
