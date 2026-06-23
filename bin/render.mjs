import fs from "node:fs";
import path from "node:path";
import process from "node:process";
import puppeteer from "puppeteer";

function fail(message, error = null) {
  console.error(message);

  if (error) {
    console.error(error.stack || error.message || error);
  }

  process.exit(1);
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

const browserOptions = {
  headless: "new",
  args: payload.browser?.args || [],
};

if (payload.browser?.executablePath) {
  browserOptions.executablePath = payload.browser.executablePath;
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
