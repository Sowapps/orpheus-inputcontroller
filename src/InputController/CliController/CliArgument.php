<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\CliController;

use Orpheus\DataType\AbstractType;
use Orpheus\DataType\BooleanType;
use Orpheus\DataType\FileType;
use Orpheus\DataType\IntegerType;
use Orpheus\DataType\StringType;
use Orpheus\Exception\UserException;

class CliArgument {
	
	/**
	 * Registered regex for a type
	 *
	 * @var array
	 */
	protected static array $typeValidators = [];
	
	/**
	 * The long name
	 *
	 * @var string
	 */
	protected string $longName;
	
	/**
	 * The short name
	 *
	 * @var string|null
	 */
	protected ?string $shortName;
	
	/**
	 * The type
	 *
	 * @var string|null
	 */
	protected ?string $type;
	
	/**
	 * Is the argument required ?
	 *
	 * @var boolean
	 */
	protected bool $required;
	
	/**
	 * Constructor
	 */
	public function __construct(string $longName, ?string $shortName, ?string $type, bool $required = false) {
		$this->longName = $longName;
		$this->shortName = $shortName;
		$this->type = $type;
		$this->required = $required;
	}
	
	/**
	 * Make a CliArgument from config
	 */
	public static function make(string $name, string $config): CliArgument {
		$required = false;
		if( $config[0] === '+' ) {
			$required = true;
			$config = substr($config, 1);
		}
		[$shortName, $type] = explodeList(':', $config, 2);
		
		return new static($name, $shortName, $type, $required);
	}
	
	public function getUsageCommand(): string {
		$param = $this->getLongCommand($this->getType(), true);
		if( !$this->isRequired() ) {
			$param = '[' . $param . ']';
		}
		
		return $param;
	}
	
	public function getLongCommand($value, $usage = false): string {
		$type = $this->getTypeValidator();
		$command = '--' . ($usage && $type->isFalsable() ? '(not-)' : '') . $this->getLongName();
		if( $value !== true ) {
			$command .= '="' . $type->format($value) . '"';
		}
		
		return $command;
	}
	
	public function isRequiringValue(): bool {
		return !$this->getTypeValidator()->isFalsable();
	}
	
	public function getValueFrom($values) {
		return $this->getTypeValidator()->getValueFrom($values, $this->getLongName(), $this->getShortName());
	}
	
	public function verify($value): bool {
		if( $value === null ) {
			if( $this->isRequired() ) {
				throw new UserException(sprintf("The parameter \"%s\" is required", $this->longName));
			} else {
				return false;
			}
		}
		$type = $this->getType();
		if( !static::validateParameter($type, $value) ) {
			throw new UserException(sprintf("The given value \"%s\" of parameter \"%s\" is not a valid value of type \"%s\"", $value, $this->longName, $type));
		}
		
		return true;
	}
	
	/**
	 * Get the long name
	 */
	public function getLongName(): string {
		return $this->longName;
	}
	
	/**
	 * Get the short name
	 */
	public function getShortName(): ?string {
		return $this->shortName;
	}
	
	/**
	 * Test argument has a short name
	 */
	public function hasShortName(): bool {
		return !!$this->shortName;
	}
	
	/**
	 * Get the type
	 */
	public function getType(): ?string {
		return $this->type;
	}
	
	/**
	 * Get the type
	 */
	public function getTypeValidator(): AbstractType {
		return static::getValidatorByType($this->type);
	}
	
	/**
	 * Is this argument required ?
	 */
	public function isRequired(): bool {
		return $this->required;
	}
	
	/**
	 * Set the required state
	 */
	public function setRequired(bool $required): CliArgument {
		$this->required = $required;
		
		return $this;
	}
	
	/**
	 * Get a type validator by type name
	 */
	public static function getValidatorByType(string $type): AbstractType {
		return static::$typeValidators[$type];
	}
	
	/**
	 * Add the type validator to validate parameters
	 */
	public static function registerTypeValidator(AbstractType $type): void {
		static::$typeValidators[$type->getName()] = $type;
	}
	
	/**
	 * Add the type validator to validate parameters
	 */
	public static function validateParameter(string $type, mixed $value): bool {
		$validator = static::getValidatorByType($type);
		
		return $validator->validate($value);
	}
	
}

CliArgument::registerTypeValidator(new StringType());
CliArgument::registerTypeValidator(new IntegerType());
CliArgument::registerTypeValidator(new BooleanType());
CliArgument::registerTypeValidator(new FileType());
