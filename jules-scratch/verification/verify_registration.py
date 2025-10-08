from playwright.sync_api import sync_playwright, Page, expect
import sys

def main():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()

        # In this sandbox, the server is likely running at the root.
        base_url = "http://localhost"
        register_url = f"{base_url}/register/"

        try:
            page.goto(register_url, timeout=10000) # Increased timeout
        except Exception as e:
            print(f"Could not connect to {register_url}. Error: {e}", file=sys.stderr)
            print("Could not verify frontend changes as the dev server seems to be unavailable at the expected URL.", file=sys.stderr)
            browser.close()
            sys.exit(1)

        # Verify that the key elements of the registration form are present.
        expect(page.get_by_role("heading", name="Register")).to_be_visible()
        expect(page.get_by_label("Name")).to_be_visible()
        expect(page.get_by_label("Email")).to_be_visible()
        expect(page.get_by_label("Password")).to_be_visible()
        expect(page.get_by_label("Referral Code (Optional)")).to_be_visible()
        expect(page.get_by_role("button", name="Register")).to_be_visible()

        # Take a screenshot
        screenshot_path = "jules-scratch/verification/registration_page.png"
        page.screenshot(path=screenshot_path)
        print(f"Screenshot saved to {screenshot_path}")

        browser.close()

if __name__ == "__main__":
    main()