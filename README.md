# FormRecode
![php](https://img.shields.io/badge/php-8.2-informational)
![api](https://img.shields.io/badge/pocketmine-5.0-informational)

A flexible form system for PocketMine-MP, allowing custom forms with elements, buttons, and event handling.

## Features

- Supports form elements such as **Labels**, **Headers**, and **Dividers**.
- Easy creation of forms with titles, descriptions, buttons, and custom elements.
- Event handling for **submit** and **close** actions using closures.
- Seamless integration with PocketMine's Player and networking system.
- Customizable buttons with optional labels and images.
- Supports JSON serialization for form data transmission.
- **Advanced protection system against spoofed responses** and form misuse.

## Installation

Place the source files in your plugin's namespace or autoload them via Composer.

## Usage

### Creating a form

You can create a form with a title, description, custom elements (like Label, Divider, Header), and buttons:

```php
use Zwuiix\FormRecode\types\Form;
use Zwuiix\FormRecode\elements\v786\Label;
use Zwuiix\FormRecode\elements\v786\Divider;
use Zwuiix\FormRecode\elements\v786\Header;
use Zwuiix\FormRecode\elements\Button;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

$form = Form::create(
    "Welcome!",
    "Here's an example of a description",
    elements: [
        new Label("Label"),
        new Divider("Divider"),
        new Header("Header")
    ]
);

// Add buttons with text and optional labels
$form->addButton("Hello!", "first");
$form->addButton("Bye!", "second");

// Handle form submission (button click)
$form->onSubmit(function (Player $player, Button $button) {
    $player->sendMessage(TextFormat::GREEN . "You pressed the button ID: {$button->getId()}, Label: {$button->getLabel()}, Text: {$button->getText()}!");
});

// Handle form closure without submission
$form->onClose(function (Player $player) {
    $player->sendMessage(TextFormat::RED . "You have closed the form.");
});

// Send the form to a player
$form->send($player);
````

## Protection Against Spoofing and Invalid Responses

The default PocketMine form system has a critical flaw: the client can send back form responses **at any time**, even long after the form has been closed or the UI no longer visible. This allows for multiple issues:

- **Spoofed responses**: A player can fake responses to forms they no longer have open.
- **Delayed responses**: Responses can be sent well after the form was closed, causing desync or unintended plugin behavior.
- **Exploits in gameplay**: For example, submitting a kit selection form while in combat, despite the UI being closed.

### How FormRecode Protects Your Plugin

FormRecode introduces a **robust multi-layer protection system** to prevent these issues, including:

#### 1. Unique Form Sessions via `FakeInventoryWindow`

- Each form is linked to a **unique, temporary inventory window** (`FakeInventoryWindow`) identified by a unique ID.
- This fake inventory acts as a **marker** ensuring the player can only respond **once** to a specific form instance.
- The server tracks if the player currently has this inventory open.
- If a player tries to send a response for a form **without having the corresponding inventory open**, the response is **rejected** and an error is triggered.
- This prevents a player from answering a form that is closed or that they never had opened, protecting against spoofing and replay attacks.

#### 2. Limiting Response Size (Too Large Protection)

- FormRecode checks the **depth and size** of incoming JSON responses.
- Responses exceeding expected complexity or size are rejected.
- This avoids malformed or intentionally crafted responses that could crash the server or exploit parsing logic.

#### 3. Strict Validation of Responses

- The server verifies that the response matches the **expected button indices and labels**.
- Invalid or out-of-range responses are ignored.
- Responses are only accepted once per form session; multiple submissions are blocked.

#### 4. Proper Handling of Form Closure

- When a player closes the form without responding, a designated **onClose** callback is triggered.
- The server marks the form session as closed and refuses any late responses.

### Example Scenario

- A player opens a "Kit Selection" form.
- FormRecode creates a unique `FakeInventoryWindow` for this form and marks it as "open" for the player.
- The player can only submit a response **while this inventory is open**.
- If the player closes the form or the inventory is closed server-side, the session is invalidated.
- Any response sent afterward will be rejected because the player no longer "has the form open".
- If the player attempts to spoof a response (e.g., send a response packet without opening the form), FormRecode rejects it immediately.

## API Overview

### Form

* `Form::create(string $title = "", string $description = "", array $buttons = [], array $elements = []): Form`
  Create a new form instance.

* `addButton(string $text, string|int|null $label = null, ?Image $image = null): void`
  Add a button to the form.

* `onSubmit(Closure $callback): Form`
  Register a callback invoked when a button is pressed.

* `onClose(Closure $callback): Form`
  Register a callback invoked when the form is closed without response.

* `send(Player $player): void`
  Sends the form to a player.

### Elements

You can use various elements to compose your form content:

* **Label** - Simple text label.
* **Header** - Header text for section titles.
* **Divider** - Visual divider with optional text.

All elements implement `JsonSerializable` for form JSON generation.

### Button

Represents clickable buttons inside the form. Buttons can have:

* ID (auto-generated)
* Label (optional identifier)
* Text (display text)
* Optional Image

## Notes

* The system uses PHP 8+ features such as union types and readonly classes.
* Make sure to handle player connection checks and JSON exceptions as in the example.
* Custom `FakeInventoryWindow` ensures form uniqueness during handling.

## License

Apache-2.0 license.